const PHP = require('php-wasm/PhpWeb').PhpWeb;
const php = new PHP;

const demo = `
<?php
ini_set('session.save_path', '/home/web_user');
session_id('fake-cookie');
session_start();
echo 'hello';
echo time();
`;

const log = `
<?php
$db = new SQLite3('people.db');
$db->query('CREATE TABLE IF NOT EXISTS people (
id INTEGER PRIMARY KEY,
name TEXT NOT NULL
);');
for($i = 0; $i < 100; $i++) {
$insert = $db->prepare('INSERT INTO people (name) VALUES(:name)');
$insert->bindValue(':name', str_repeat(chr($i+64), 10), SQLITE3_TEXT);
$insert->execute();
}
$results = $db->query('SELECT * FROM people WHERE id=1');
$rows = [];
while ($row = $results->fetchArray()) {
dd(json_encode($row));

}
`;



php.addEventListener('ready', () => {
    ready();
    runPhp(log);
    //loopedPhp(log, 3500);
    //loopedPhp(demo, 4500);
});

php.addEventListener('output', (event) => {
    console.log(event.detail);
    delayedResult(event.detail[0]);
});

async function runPhp(phpscript, ms) {
    php.run(phpscript);
    console.log('PHP executed..');
}

async function loopedPhp(phpscript, ms) {
    const msMinus = ms - 1000;
    console.log('Loop started..');
    php.run(phpscript);
    console.log('PHP executed..');
    setTimeout(() => console.log('Ending loop in 1 second..'), msMinus);
    setTimeout(() => loopedPhp(phpscript, ms), ms);
}

function ready() {
    writeResult('<h1>Ready</h1>')
}

async function delayedResult(details) {
    console.log('Writing result in 1100ms: ' + event.detail);
    setTimeout(() => writeResult(details), 1100);
}

function writeResult(detail) {
    const resultFrame = document.getElementById("result");
    var outputBase = resultFrame.srcdoc;
    var output = document.createElement('div');
    output.innerHTML = resultFrame.srcdoc;
    document.getElementById("result").srcdoc = detail;
}
