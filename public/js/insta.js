if (
    ((function (e, t) {
        "use strict";
        var i = "addEventListener",
            n = "getElementsByClassName",
            o = "createElement",
            a = "classList",
            s = "appendChild",
            d = "dataset",
            l = "embed-lightbox-iframe",
            m = "embed-lightbox-is-loaded",
            r = "embed-lightbox-is-opened",
            c = "embed-lightbox-is-showing",
            h = function (e, i, o) {
                var a = o || {};
                (this.trigger = e),
                    (this.elemRef = i),
                    (this.rate = a.rate || 500),
                    (this.el = t[n](l)[0] || ""),
                    (this.body = this.el ? this.el[n]("embed-lightbox-body")[0] : ""),
                    (this.content = this.el ? this.el[n]("embed-lightbox-content")[0] : ""),
                    (this.href = e[d].src || ""),
                    (this.paddingBottom = e[d].paddingBottom || ""),
                    (this.onOpened = a.onOpened),
                    (this.onIframeLoaded = a.onIframeLoaded),
                    (this.onLoaded = a.onLoaded),
                    (this.onCreated = a.onCreated),
                    this.init();
            };
        (h.prototype.init = function () {
            var e = this;
            this.el || this.create();
            var t,
                n,
                o,
                a,
                s,
                d,
                l =
                    ((t = function (t) {
                        t.preventDefault(), e.open();
                    }),
                    (n = this.rate),
                    function () {
                        (s = this), (a = [].slice.call(arguments, 0)), (d = new Date());
                        var e = function () {
                            var i = new Date() - d;
                            i < n ? (o = setTimeout(e, n - i)) : ((o = null), t.apply(s, a));
                        };
                        o || (o = setTimeout(e, n));
                    });
            this.trigger[i]("click", l);
        }),
            (h.prototype.create = function () {
                var e = this,
                    n = t[o]("div");
                (this.el = t[o]("div")),
                    (this.content = t[o]("div")),
                    (this.body = t[o]("div")),
                    this.el[a].add(l),
                    n[a].add("embed-lightbox-backdrop"),
                    this.content[a].add("embed-lightbox-content"),
                    this.body[a].add("embed-lightbox-body"),
                    this.el[s](n),
                    this.content[s](this.body),
                    (this.contentHolder = t[o]("div")),
                    this.contentHolder[a].add("embed-lightbox-content-holder"),
                    this.contentHolder[s](this.content),
                    this.el[s](this.contentHolder),
                    t.body[s](this.el),
                    n[i]("click", function () {
                        e.close();
                    });
                var d = function () {
                    e.isOpen() || (e.el[a].remove(c), (e.body.innerHTML = ""));
                };
                this.el[i]("transitionend", d, !1), this.el[i]("webkitTransitionEnd", d, !1), this.el[i]("mozTransitionEnd", d, !1), this.el[i]("msTransitionEnd", d, !1), this.callCallback(this.onCreated, this);
            }),
            (h.prototype.loadIframe = function () {
                var e,
                    i,
                    n = this;
                (this.iframeId = l + "-" + this.elemRef),
                    (this.body.innerHTML =
                        '<iframe src="' +
                        this.href +
                        '" name="' +
                        this.iframeId +
                        '" id="' +
                        this.iframeId +
                        '" onload="this.style.opacity=1;" style="opacity:0;border:none;" scrolling="no" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" height="166" frameborder="no"></iframe>'),
                    (e = this.iframeId),
                    (i = this.body),
                    (t.getElementById(e).onload = function () {
                        (this.style.opacity = 1), i[a].add(m), n.callCallback(n.onIframeLoaded, n), n.callCallback(n.onLoaded, n);
                    });
            }),
            (h.prototype.open = function () {
                this.loadIframe(), this.paddingBottom ? (this.content.style.paddingBottom = this.paddingBottom) : this.content.removeAttribute("style"), this.el[a].add(c), this.el[a].add(r), this.callCallback(this.onOpened, this);
            }),
            (h.prototype.close = function () {
                this.el[a].remove(r), this.body[a].remove(m);
            }),
            (h.prototype.isOpen = function () {
                return this.el[a].contains(r);
            }),
            (h.prototype.callCallback = function (e, t) {
                "function" == typeof e && e.bind(this)(t);
            }),
            (e.EmbedSocialIframeLightbox = h);
    })("undefined" != typeof window ? window : this, document),
    !document.getElementById("EmbedSocialIFrame"))
) {
    var jsEmbed = document.createElement("script");
    (jsEmbed.id = "EmbedSocialIFrame"), (jsEmbed.src = "http://192.168.0.98/Thecollectivecoven/assets/js/iframe.js"), document.getElementsByTagName("body")[0].appendChild(jsEmbed);
}
if (!document.getElementById("EmbedSocialIFrameLightboxCSS")) {
    var cssEmbed = document.createElement("link");
    (cssEmbed.id = "EmbedSocialIFrameLightboxCSS"), (cssEmbed.rel = "stylesheet"), (cssEmbed.href = "http://192.168.0.98/Thecollectivecoven/assets/css/insta.css"), document.getElementsByTagName("head")[0].appendChild(cssEmbed);
}
if (
    ((EMBEDSOCIALINSTAGRAM = {
        getEmbedData: function (e, t) {
            if (t.getElementsByTagName("iframe").length <= 0) {
                var i = document.createElement("iframe"),
                    n = "https://embedsocial.com/api/pro_album/instagram/" + e;
                i.setAttribute("src", n),
                    i.setAttribute("id", "embedIFrame_" + e + Math.random().toString(36).substring(7)),
                    (i.style.width = "0px"),
                    (i.style.height = "0px"),
                    (i.style.maxHeight = "100%"),
                    (i.style.maxWidth = "100%"),
                    (i.style.minHeight = "100%"),
                    (i.style.minWidth = "100%"),
                    (i.style.border = "0"),
                    i.setAttribute("class", "embedsocial-in-album-iframe"),
                    i.setAttribute("scrolling", "no"),
                    t.appendChild(i),
                    EMBEDSOCIALINSTAGRAM.initResizeFrame(e);
            }
        },
        initResizeFrame: function (e) {
            document.getElementById("EmbedSocialIFrame") && "function" == typeof iFrameResize
                ? (iFrameResize(
                      {
                          messageCallback: function (e) {
                              "create" == e.message.action && EMBEDSOCIALINSTAGRAM.createLightBox(e.message),
                                  e.message.hasOwnProperty("navigationCode") && EMBEDSOCIALINSTAGRAM.navigationLightBox(e.message.albumRef, e.message.navigationCode);
                          },
                      },
                      ".embedsocial-in-album-iframe"
                  ),
                  iFrameResize(
                      {
                          messageCallback: function (e) {
                              "close" == e.message.action && EMBEDSOCIALINSTAGRAM.closeLightBox(e.message), e.message.hasOwnProperty("navigationCode") && EMBEDSOCIALINSTAGRAM.navigationLightBox(e.message.albumRef, e.message.navigationCode);
                          },
                          sizeHeight: !1,
                          sizeWidth: !1,
                      },
                      "#embed-lightbox-iframe-" + e
                  ))
                : setTimeout(function () {
                      EMBEDSOCIALINSTAGRAM.initResizeFrame(e);
                  }, 200);
        },
        createLightBox: function (e) {
            if (document.getElementById("embed-lightbox-" + e.albumRef)) (t = document.getElementById("embed-lightbox-" + e.albumRef)).setAttribute("data-src", e.src), (t.innerHTML = "");
            else {
                var t = document.createElement("a");
                t.setAttribute("class", "embedsocail-iframe-lightbox-link"),
                    t.setAttribute("id", "embed-lightbox-" + e.albumRef),
                    t.setAttribute("data-padding-bottom", "56%"),
                    t.setAttribute("data-src", e.src),
                    document.body.appendChild(t);
            }
            [].forEach.call(document.getElementsByClassName("embedsocail-iframe-lightbox-link"), function (t) {
                t.lightbox = new EmbedSocialIframeLightbox(t, e.albumRef, {
                    onLoaded: function (t) {
                        function i() {
                            var t = window.innerHeight || document.documentElement.clientHeight,
                                i = document.getElementById("embed-lightbox-iframe-" + e.albumRef);
                            t > 1800 && (t = 1800), null != i && ((i.style.height = parseInt(0.95 * t) + "px"), (i.style.zIndex = "1"));
                        }
                        EMBEDSOCIALINSTAGRAM.initResizeFrame(e.albumRef), i(), window.addEventListener("resize", i);
                    },
                });
            }),
                EMBEDSOCIALINSTAGRAM.openLightBox(e);
        },
        openLightBox: function (e) {
            document.getElementById("embed-lightbox-" + e.albumRef).click(),
                document.getElementsByClassName("embed-lightbox-body")[0].addEventListener("click", function (t) {
                    (t.target || t.srcElement).classList.contains("embed-lightbox-is-loaded") && EMBEDSOCIALINSTAGRAM.closeLightBox(e);
                });
        },
        closeLightBox: function (e) {
            document.getElementsByClassName("embed-lightbox-backdrop")[0].click(),
                [].forEach.call(document.getElementsByClassName("embed-lightbox-body"), function (e) {
                    e.className = "embed-lightbox-body";
                });
        },
        navigationLightBox: function (e, t) {
            var i = document.getElementById("embed-lightbox-iframe-" + e);
            i && i.iFrameResizer.sendMessage({ navigationCode: t });
        },
    }),
    "IntersectionObserver" in window)
)
    function callVisible(e, t) {
        for (i in e) e[i].isIntersecting && EMBEDSOCIAL.getEmbedData(e[i].target.getAttribute("data-ref"), e[i].target);
    }
function standardLoad(e) {
    for (i = 0; i < e.length; i++) {
        var t = e[i],
            n = t.getAttribute("data-ref");
        "yes" === t.getAttribute("data-lazyload") && "IntersectionObserver" in window ? new IntersectionObserver(callVisible, {}).observe(t) : EMBEDSOCIALINSTAGRAM.getEmbedData(n, t);
    }
}
var er = document.getElementsByClassName("embedsocial-instagram");
er.length > 0
    ? standardLoad(er)
    : window.addEventListener("load", function () {
          standardLoad(document.getElementsByClassName("embedsocial-instagram"));
      });