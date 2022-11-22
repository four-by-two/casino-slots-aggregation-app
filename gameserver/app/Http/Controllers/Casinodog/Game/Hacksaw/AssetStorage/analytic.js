const GA_KEY_PROD = 'UA-112538731-3';
const GA_KEY_STAGING = 'UA-112538731-4';
const GA_KEY_DEV = 'UA-112538731-2';

let Hacksaw = window.hacksawCasino;
let _uiChannel;
let _casinoChannel;
let _trackingEnabled = false;
let _gameObj = {};
let _gaKey = '';
let isProd = false;
let _startTime = new Date().getTime();

subscribe();

function createGaSnippet() {
    switch (_gameObj.deployEnv) {
        case 'prod':
            _gaKey = GA_KEY_PROD;
            break;
        case 'staging':
            _gaKey = GA_KEY_STAGING;
            break;
        case 'dev':
            _gaKey = GA_KEY_DEV;
            break;
        default:
            _gaKey = '';
    }

    // just in case
    if (_gaKey == '') {
        _trackingEnabled = false;
        return;
    }

    let scriptHolder = document.createElement('script');
    let gaScript = document.createElement('script');
    let pageTitle = _gameObj.gameName + " - " + _gameObj.mode;
    let pagePath = pageTitle.replace(/\s-\s|\s/g, '-').toLowerCase();
    let referrer = extractUrlOrigin(document.referrer);

    scriptHolder.appendChild(document.createTextNode("\
        window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;\
        ga('create', '" + _gaKey + "', 'auto');\
        ga('require', 'ec');\
        ga('set', 'language', '"+ _gameObj.language + "');\
        ga('set', 'currencyCode', '"+ _gameObj.currency + "');\
        ga('set', 'referrer', '"+ referrer + "');\
        ga('set', 'title', '"+ pageTitle + "');\
        ga('ec:addProduct', {'id': '"+ _gameObj.gameId + "_" + _gameObj.mode + "', 'name': '" + _gameObj.gameName + " - " + _gameObj.mode + "', 'category': '" + _gameObj.gameType + " - " + _gameObj.mode + "', 'variant': '" + _gameObj.channel + "'});\
        ga('send', 'pageview', '/"+ pagePath + "');\
    "));

    gaScript.src = 'https://www.google-analytics.com/analytics.js';
    gaScript.async = true;

    document.head.appendChild(scriptHolder);
    document.head.appendChild(gaScript);
}

function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

function extractUrlOrigin(url) {
    var pathArray = url.split('/');
    var protocol = pathArray[0];
    var host = pathArray[2];
    var url = protocol + '//' + host;

    return url;
}

function subscribe() {
    if (typeof Hacksaw !== 'undefined') {
        _uiChannel = Hacksaw.PubSub.getChannel('ui');
        _casinoChannel = Hacksaw.PubSub.getChannel('casino');

        _casinoChannel.subscribe('initData', data => {
            _gameObj.deployEnv = data.deployEnv;
            _gameObj.gameName = data.gameName.replace(/'/g, '');
            _gameObj.gameId = data.gameId;
            _gameObj.channel = data.channel;
            _gameObj.partner = data.partner;
            _gameObj.gameType = data.gameType;
            _gameObj.branding = data.branding;
            _gameObj.mode = data.mode;
            _gameObj.currency = (data.currency == 'FUN') ? 'EUR' : data.currency;
            _gameObj.language = data.language.toLowerCase();

            // Add branding to gameName if it's not default
            if (_gameObj.branding != 'default') {
                _gameObj.gameName = _gameObj.gameName + " - " + _gameObj.branding;
            }

            // Don't track local environments or CI partners
            let regx = new RegExp("_ci$|_test$");
            let isCi = regx.test(_gameObj.partner);
            if (_gameObj.deployEnv !== '{local}' && isCi == false) {
                _trackingEnabled = true;
                createGaSnippet();
            }
        });

        _casinoChannel.subscribe('gameLoaded', data => {
            var currentTime = new Date().getTime();
            setTimeout(() => {
                if (_trackingEnabled) {
                    ga('send', {
                        'hitType': 'timing',
                        'timingCategory': 'Game loaded - ' + _gameObj.gameName + ', ' + _gameObj.channel,
                        'timingVar': 'load',
                        'timingValue': parseInt(currentTime - _startTime)
                    });
                }
            }, 2000);
        });

        _casinoChannel.subscribe('betAccepted', data => {
            if (_trackingEnabled) {
                let betAmount = 0.00;
                if (data.betAmountString != null) {
                    betAmount = (parseInt(data.betAmountString) / 100).toFixed(2);
                }
                let productData = {
                    'id': _gameObj.gameId + '_' + _gameObj.mode,
                    'name': _gameObj.gameName + ' - ' + _gameObj.mode,
                    'category': _gameObj.gameType + ' - ' + _gameObj.mode,
                    'variant': _gameObj.channel,
                    'price': betAmount,
                    'quantity': 1
                };
                let purchaseData = {
                    'id': String(uuidv4() + '-' + data.roundId + '-' + _gameObj.mode),
                    'revenue': String(betAmount),
                    'affiliation': String(_gameObj.partner + ' - ' + _gameObj.mode)
                };

                if (data.offerId != null) {
                    productData['coupon'] = String(data.offerId);
                    purchaseData['coupon'] = String(data.offerId);
                }

                ga('ec:addProduct', productData);
                ga('ec:setAction', 'purchase', purchaseData);
                ga('send', {
                    'hitType': 'event',
                    'eventCategory': _gameObj.gameName + ' - ' + _gameObj.mode,
                    'eventAction': 'Bet accepted'
                });
            }
        });

        _casinoChannel.subscribe('startAutoplay', data => {
            if (_trackingEnabled) {
                ga('send', {
                    'hitType': 'event',
                    'eventCategory': _gameObj.gameName + ' - ' + _gameObj.mode,
                    'eventAction': 'Start autoplay',
                    'eventValue': parseInt(data.autoplayRounds)
                });
            }
        });

        _casinoChannel.subscribe('logError', data => {
            if (_trackingEnabled) {
                ga('send', {
                    'hitType': 'event',
                    'eventCategory': _gameObj.gameName + ' - ' + _gameObj.mode,
                    'eventAction': 'Console error'
                });
            }
        });

        _casinoChannel.subscribe('enterFreeRoundMode', data => {
            if (_trackingEnabled) {
                ga('send', {
                    'hitType': 'event',
                    'eventCategory': _gameObj.gameName + ' - ' + _gameObj.mode,
                    'eventAction': 'Enter free round mode'
                });
            }
        });

        _uiChannel.subscribe('replayRound', data => {
            if (_trackingEnabled) {
                if (data.replayRound == true) {
                    ga('send', {
                        'hitType': 'event',
                        'eventCategory': _gameObj.gameName + ' - ' + _gameObj.mode,
                        'eventAction': 'Accept replay round'
                    });
                } else {
                    ga('send', {
                        'hitType': 'event',
                        'eventCategory': _gameObj.gameName + ' - ' + _gameObj.mode,
                        'eventAction': 'Decline replay round'
                    });
                }
            }
        });
    } else {
        setTimeout(() => {
            subscribe();
        }, 11111);
    }
}
// WEBPACK FOOTER //
// ./src/analytics.js