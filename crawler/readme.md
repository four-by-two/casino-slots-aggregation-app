
## Getting Started

Browsertrix Crawler requires [Docker](https://docs.docker.com/get-docker/) to be installed on the machine running the crawl.

Assuming Docker is installed, you can run a crawl and test your archive with the following steps.

You don't even need to clone this repo, just choose a directory where you'd like the crawl data to be placed, and then run
the following commands. Replace `[URL]` with the web site you'd like to crawl.

1. Run `docker pull webrecorder/browsertrix-crawler`
2. `docker run -v $PWD/crawls:/crawls/ -it webrecorder/browsertrix-crawler crawl --url [URL] --generateWACZ --text --collection test`
3. The crawl will now run and progress of the crawl will be output to the console. Depending on the size of the site, this may take a bit!
4. Once the crawl is finished, a WACZ file will be created in `crawls/collection/test/test.wacz` from the directory you ran the crawl!
5. You can go to [ReplayWeb.page](https://replayweb.page) and open the generated WACZ file and browse your newly crawled archive!

Here's how you can use some of the command-line options to configure the crawl:

- To include automated text extraction for full text search, add the `--text` flag.

- To limit the crawl to a maximum number of pages, add `--limit P` where P is the number of pages that will be crawled.

- To limit the crawl to a maximum size, set `--sizeLimit` (size in bytes)

- To limit the crawl time, set `--timeLimit` (in seconds)

- To run more than one browser worker and crawl in parallel, and `--workers N` where N is number of browsers to run in parallel. More browsers will require more CPU and network bandwidth, and does not guarantee faster crawling.

- To crawl into a new directory, specify a different name for the `--collection` param, or, if omitted, a new collection directory based on current time will be created. Adding the `--overwrite` flag will delete the collection directory at the start of the crawl, if it exists.

Browsertrix Crawler includes a number of additional command-line options, explained below.

## Crawling Configuration Options


<details>
      <summary><b>The Browsertrix Crawler docker image currently accepts the following parameters:</b></summary>

```
      --help                                Show help                  [boolean]
      --version                             Show version number        [boolean]
      --seeds, --url                        The URL to start crawling from
                                                           [array] [default: []]
      --seedFile, --urlFile                 If set, read a list of seed urls,
                                            one per line, from the specified
                                                                        [string]
  -w, --workers                             The number of workers to run in
                                            parallel       [number] [default: 1]
      --crawlId, --id                       A user provided ID for this crawl or
                                            crawl configuration (can also be set
                                            via CRAWL_ID env var)
                                              [string] [default: <hostname> or CRAWL_ID env variable]
      --newContext                          The context for each new capture,
                                            can be a new: page, window, session
                                            or browser.
                                                      [string] [default: "page"]
      --waitUntil                           Puppeteer page.goto() condition to
                                            wait for before continuing, can be
                                            multiple separate by ','
                                                  [default: "load,networkidle2"]
      --depth                               The depth of the crawl for all seeds
                                                          [number] [default: -1]
      --extraHops                           Number of extra 'hops' to follow,
                                            beyond the current scope
                                                           [number] [default: 0]
      --limit                               Limit crawl to this number of pages
                                                           [number] [default: 0]
      --timeout                             Timeout for each page to load (in
                                            seconds)      [number] [default: 90]
      --scopeType                           A predfined scope of the crawl. For
                                            more customization, use 'custom' and
                                            set scopeIncludeRx regexes
       [string] [choices: "page", "page-spa", "prefix", "host", "domain", "any",
                                                                       "custom"]
      --scopeIncludeRx, --include           Regex of page URLs that should be
                                            included in the crawl (defaults to
                                            the immediate directory of URL)
      --scopeExcludeRx, --exclude           Regex of page URLs that should be
                                            excluded from the crawl.
      --allowHashUrls                       Allow Hashtag URLs, useful for
                                            single-page-application crawling or
                                            when different hashtags load dynamic
                                            content
      --blockRules                          Additional rules for blocking
                                            certain URLs from being loaded, by
                                            URL regex and optionally via text
                                            match in an iframe
                                                           [array] [default: []]
      --blockMessage                        If specified, when a URL is blocked,
                                            a record with this error message is
                                            added instead               [string]
  -c, --collection                          Collection name to crawl to (replay
                                            will be accessible under this name
                                            in pywb preview)
                                                 [string] [default: "crawl-@ts"]
      --headless                            Run in headless mode, otherwise
                                            start xvfb[boolean] [default: false]
      --driver                              JS driver for the crawler
                                     [string] [default: "/app/defaultDriver.js"]
      --generateCDX, --generatecdx,         If set, generate index (CDXJ) for
      --generateCdx                         use with pywb after crawl is done
                                                      [boolean] [default: false]
      --combineWARC, --combinewarc,         If set, combine the warcs
      --combineWarc                                   [boolean] [default: false]
      --rolloverSize                        If set, declare the rollover size
                                                  [number] [default: 1000000000]
      --generateWACZ, --generatewacz,       If set, generate wacz
      --generateWacz                                  [boolean] [default: false]
      --logging                             Logging options for crawler, can
                                            include: stats, pywb, behaviors,
                                            behaviors-debug
                                                     [string] [default: "stats"]
      --text                                If set, extract text to the
                                            pages.jsonl file
                                                      [boolean] [default: false]
      --cwd                                 Crawl working directory for captures
                                            (pywb root). If not set, defaults to
                                            process.cwd()
                                                   [string] [default: "/crawls"]
      --mobileDevice                        Emulate mobile device by name from:
                                            https://github.com/puppeteer/puppete
                                            er/blob/main/src/common/DeviceDescri
                                            ptors.ts                    [string]
      --userAgent                           Override user-agent with specified
                                            string                      [string]
      --userAgentSuffix                     Append suffix to existing browser
                                            user-agent (ex: +MyCrawler,
                                            info@example.com)           [string]
      --useSitemap, --sitemap               If enabled, check for sitemaps at
                                            /sitemap.xml, or custom URL if URL
                                            is specified
      --statsFilename                       If set, output stats as JSON to this
                                            file. (Relative filename resolves to
                                            crawl working directory)
      --behaviors                           Which background behaviors to enable
                                            on each page
                           [string] [default: "autoplay,autofetch,siteSpecific"]
      --behaviorTimeout                     If >0, timeout (in seconds) for
                                            in-page behavior will run on each
                                            page. If 0, a behavior can run until
                                            finish.       [number] [default: 90]
      --profile                             Path to tar.gz file which will be
                                            extracted and used as the browser
                                            profile                     [string]
      --screencastPort                      If set to a non-zero value, starts
                                            an HTTP server with screencast
                                            accessible on this port
                                                           [number] [default: 0]
      --screencastRedis                     If set, will use the state store
                                            redis pubsub for screencasting.
                                            Requires --redisStoreUrl to be set
                                                      [boolean] [default: false]
      --warcInfo, --warcinfo                Optional fields added to the
                                            warcinfo record in combined WARCs
      --redisStoreUrl                       If set, url for remote redis server
                                            to store state. Otherwise, using
                                            in-memory store             [string]
      --saveState                           If the crawl state should be
                                            serialized to the crawls/ directory.
                                            Defaults to 'partial', only saved
                                            when crawl is interrupted
           [string] [choices: "never", "partial", "always"] [default: "partial"]
      --saveStateInterval                   If save state is set to 'always',
                                            also save state during the crawl at
                                            this interval (in seconds)
                                                         [number] [default: 300]
      --saveStateHistory                    Number of save states to keep during
                                            the duration of a crawl
                                                           [number] [default: 5]
      --sizeLimit                           If set, save state and exit if size
                                            limit exceeds this value
                                                           [number] [default: 0]
      --timeLimit                           If set, save state and exit after
                                            time limit, in seconds
                                                           [number] [default: 0]
      --healthCheckPort                     port to run healthcheck on
                                                           [number] [default: 0]
      --overwrite                           overwrite current crawl data: if
                                            set, existing collection directory
                                            will be deleted before crawl is
                                            started   [boolean] [default: false]
      --config                              Path to YAML config file
```
</details>


### Waiting for Page Load

One of the key nuances of browser-based crawling is determining when a page is finished loading. This can be configured with the `--waitUntil` flag.

The default is `load,networkidle2`, which waits until page load and <=2 requests remain, but for static sites, `--wait-until domcontentloaded` may be used to speed up the crawl (to avoid waiting for ads to load for example). The `--waitUntil networkidle0` may make sense for sites, where absolutely all requests must be waited until before proceeding.

See [page.goto waitUntil options](https://pptr.dev/api/puppeteer.page.goto#remarks) for more info on the options that can be used with this flag from the Puppeteer docs.

