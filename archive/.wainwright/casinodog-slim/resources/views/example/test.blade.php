<!DOCTYPE html>
<html>
<body>

<h1>The Window Object</h1>
<h2>The atob() Method</h2>

<p>The atob() method decodes a base-64 encoded string.</p>
<p>The atob() method is not supported in IE9 and earlier.</p>
<p id="demo"></p>
<iframe src="{{ $data['url'] }}" style="width: 1000px; height: 600px;"></iframe>
<script>


fetch('https://wainwrighted.herokuapp.com/<?php echo $data['url']; ?>')
    .then(function(response) {
        // When the page is loaded convert it to text
        return response.text()
    })
    .then(function(html) {
        // Initialize the DOM parser
        var parser = new DOMParser();

        // Parse the text
        var doc = parser.parseFromString(html, "text/html");

        // You can now even select part of that html as you would in the regular DOM 
        // Example:
        var docArticle = doc.querySelector('body').innerHTML;
        let text = html;
        let encoded = window.btoa(text);
        let decoded = window.atob(encoded);
    

        const data = { username: encoded };

        document.getElementById("demo").innerHTML = "Encoded: " + encoded + "<br>";
        fetch('/api/testing/post_encoded', {
        method: 'POST', // or 'PUT'
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
        })
        .then((response) => response.json())
        .then((data) => {
            console.log('Success:', data);
        })
        .catch((error) => {
            console.error('Error:', error);
        });


        
        console.log(doc);
    })
    .catch(function(err) {  
        console.log('Failed to fetch page: ', err);  
    });

</script>


</body>
</html>
