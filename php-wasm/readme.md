## php-wasm
Just an example of php-wasm (very much just playing around with this till later stage) executing client-side php inside visitor's browser.

## Future Usecase
Mainly to experiment on WASM with main goals to explore following topics:
- browser side php logging
- sending hidden requests/responses (f.e. through local email and/or minio) within browser, like game-events
- hidden retrieval and processing of any possible changes in HTML Dom (using rulesets)
- p2p sharing of game assets that may be changed
- p2p sharing of game entry stages using [webarc](https://replayweb.page) format
- p2p sharing of game results between other players using [webarc](https://replayweb.page) format

pragmaticplay replay system is most likely example of the webarc format and performance (https://replayweb.page)[https://replayweb.page]

existing implementation of the latter 2 ++ decryption/encryption of messages is used in production by softswiss/pragmaticplay, you can download client bins/sdks at (dl-mio.s7s.ai)[https://dl-mio.s7s.ai)
