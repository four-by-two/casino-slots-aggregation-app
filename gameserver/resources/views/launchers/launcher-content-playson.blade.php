@if(!isset($_GET['gameName']))
    @php
        $url = "?".$_SERVER['QUERY_STRING'].'&'.$game_content['query'];
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: '.$url);
        die();
    @endphp
@else 
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
{!! $game_content['html'] !!}
<script defer="">
    
function GameMessenger( event_callbacks, origin ) {
    /**
     * if origin === undefined, listens messages from all domains
     * if origin !== undefined, listens messages only origin(s) domain(s), additional origin you can added from method .addOrigin(origin)
     */
    if ( origin !== undefined ) {
        this.origins = [];
        this.origins.push( origin );
    }
    this.events = event_callbacks;
    this.listenPostMessages();
}

GameMessenger.prototype.listenPostMessages = function () {
    var self = this;
    if ( typeof window.addEventListener !== 'undefined' ) {
        window.addEventListener( 'message', function ( e ) {
            if ( self.origins && self.origins.indexOf( e.origin ) === -1 ) {
                return;
            }
            try {
                var data = JSON.parse( e.data );
                console.log(data);
            } catch ( e ) {
                return false;
            }
            if ( self.events[data.event] ) {
                self.events[data.event](data.data || '', data.viewid || 'empty');
            }
        }, false );
    } else if ( typeof window.attachEvent !== 'undefined' ) {
        window.attachEvent( 'onmessage', function ( e ) {
            if ( self.origins && self.origins.indexOf( e.origin ) === -1 ) {
                return;
            }
            try {
                var data = JSON.parse( e.data );
                console.log(data);
            } catch ( e ) {
                return false;
            }
            if ( self.events[data.event] ) {
                self.events[data.event](data.data || '', data.viewid || 'empty');
            }
        } );
    }
};

GameMessenger.prototype.addOrigin = function ( origin ) {
    if ( this.origins === undefined ) {
        this.origins = [];
    }
    this.origins.push( origin );
};


</script>
@endif
