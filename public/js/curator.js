/**
* UTC
* Default feed Layout
* 2020-11-26 09:57:00
*/
(function () {
    // Loader
    var loader = new function(){this.rC=-1;this.r=[];this.add=function(src){this.r.push(src);};this.addTag=function(src,callback){var head=document.getElementsByTagName('head')[0],tag=src.indexOf('.js')>0?'script':'link',s=document.createElement(tag);head.appendChild(s);s.onload=callback;if(tag==='script'){s.type='text/javascript';s.src=src;}else if(tag==='link'){s.rel='stylesheet';s.href=src;}};this.loadNext=function(){this.rC++;if(this.rC>=this.r.length){this.done();}else{var r=this.r[this.rC];this.addTag(r,this.loadNext.bind(this));}};this.done=function(){this.onResourcesLoaded(window.Curator);};this.load=function(cb){this.onResourcesLoaded=cb;this.loadNext();};};

    // Config
    var config = {"post":{"template":"post-general","animate":true,"maxHeight":0,"showTitles":true,"showShare":true,"showComments":false,"showLikes":false,"autoPlayVideos":false,"clickAction":"open-popup","clickReadMoreAction":"open-popup"},"widget":{"template":"widget-waterfall","colWidth":250,"colGutter":0,"showLoadMore":true,"continuousScroll":false,"postsPerPage":12,"animate":false,"progressiveLoad":false,"lazyLoad":false,"autoLoadNew":false},"lang":"en","container":"#curator-feed-default-feed-layout","debug":0,"hidePoweredBy":true,"forceHttps":false,"feed":{"id":"9a06850d-aef5-4ce8-9c48-8e85f14c0aef","apiEndpoint":"https:\/\/api.curator.io\/v1.1","postsPerPage":12,"params":{},"limit":25,"showAds":true},"popup":{"template":"popup","templateWrapper":"popup-wrapper","autoPlayVideos":false,"deepLink":false},"filter":{"template":"filter","showNetworks":false,"showSources":false,"showAll":false,"default":"all","limitPosts":false,"limitPostNumber":0,"period":""},"type":"Waterfall","theme":"sydney"};
    var colours = {"widgetBgColor":"transparent","bgColor":"#ffffff","borderColor":"#cccccc","iconColor":"#222222","textColor":"#222222","linkColor":"#999999","dateColor":"#000000"};
    var styles = {};

    // Bootstrap
    function loaderCallback () {
        window.Curator.loadWidget(config, colours, styles);
    }

    // Run Loader
    loader.add('https://cdn.curator.io/4.1/css/curator.embed.css');
    loader.add('https://cdn.curator.io/published-css/9a06850d-aef5-4ce8-9c48-8e85f14c0aef.css');

    loader.add('http://192.168.0.98/Thecollectivecoven/assets/js/curator-embed.js');

    

    loader.load(loaderCallback);
})();