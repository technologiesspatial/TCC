window.Curator = (function (t) {
    var e = {};
    function n(o) {
        if (e[o]) return e[o].exports;
        var r = (e[o] = { i: o, l: !1, exports: {} });
        return t[o].call(r.exports, r, r.exports, n), (r.l = !0), r.exports;
    }
    return (
        (n.m = t),
        (n.c = e),
        (n.d = function (t, e, o) {
            n.o(t, e) || Object.defineProperty(t, e, { enumerable: !0, get: o });
        }),
        (n.r = function (t) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, { value: "Module" }), Object.defineProperty(t, "__esModule", { value: !0 });
        }),
        (n.t = function (t, e) {
            if ((1 & e && (t = n(t)), 8 & e)) return t;
            if (4 & e && "object" == typeof t && t && t.__esModule) return t;
            var o = Object.create(null);
            if ((n.r(o), Object.defineProperty(o, "default", { enumerable: !0, value: t }), 2 & e && "string" != typeof t))
                for (var r in t)
                    n.d(
                        o,
                        r,
                        function (e) {
                            return t[e];
                        }.bind(null, r)
                    );
            return o;
        }),
        (n.n = function (t) {
            var e =
                t && t.__esModule
                    ? function () {
                          return t.default;
                      }
                    : function () {
                          return t;
                      };
            return n.d(e, "a", e), e;
        }),
        (n.o = function (t, e) {
            return Object.prototype.hasOwnProperty.call(t, e);
        }),
        (n.p = ""),
        n((n.s = 12))
    );
})([
    function (t, e, n) {
        "use strict";
        var o = n(7),
            r = "object" == typeof self && self && self.Object === Object && self,
            i = o.a || r || Function("return this")();
        e.a = i;
    },
    ,
    function (t, e, n) {
        "use strict";
        (function (t) {
            var n = (function () {
                    if ("undefined" != typeof Map) return Map;
                    function t(t, e) {
                        var n = -1;
                        return (
                            t.some(function (t, o) {
                                return t[0] === e && ((n = o), !0);
                            }),
                            n
                        );
                    }
                    return (function () {
                        function e() {
                            this.__entries__ = [];
                        }
                        return (
                            Object.defineProperty(e.prototype, "size", {
                                get: function () {
                                    return this.__entries__.length;
                                },
                                enumerable: !0,
                                configurable: !0,
                            }),
                            (e.prototype.get = function (e) {
                                var n = t(this.__entries__, e),
                                    o = this.__entries__[n];
                                return o && o[1];
                            }),
                            (e.prototype.set = function (e, n) {
                                var o = t(this.__entries__, e);
                                ~o ? (this.__entries__[o][1] = n) : this.__entries__.push([e, n]);
                            }),
                            (e.prototype.delete = function (e) {
                                var n = this.__entries__,
                                    o = t(n, e);
                                ~o && n.splice(o, 1);
                            }),
                            (e.prototype.has = function (e) {
                                return !!~t(this.__entries__, e);
                            }),
                            (e.prototype.clear = function () {
                                this.__entries__.splice(0);
                            }),
                            (e.prototype.forEach = function (t, e) {
                                void 0 === e && (e = null);
                                for (var n = 0, o = this.__entries__; n < o.length; n++) {
                                    var r = o[n];
                                    t.call(e, r[1], r[0]);
                                }
                            }),
                            e
                        );
                    })();
                })(),
                o = "undefined" != typeof window && "undefined" != typeof document && window.document === document,
                r = void 0 !== t && t.Math === Math ? t : "undefined" != typeof self && self.Math === Math ? self : "undefined" != typeof window && window.Math === Math ? window : Function("return this")(),
                i =
                    "function" == typeof requestAnimationFrame
                        ? requestAnimationFrame.bind(r)
                        : function (t) {
                              return setTimeout(function () {
                                  return t(Date.now());
                              }, 1e3 / 60);
                          };
            var s = ["top", "right", "bottom", "left", "width", "height", "size", "weight"],
                a = "undefined" != typeof MutationObserver,
                c = (function () {
                    function t() {
                        (this.connected_ = !1),
                            (this.mutationEventsAdded_ = !1),
                            (this.mutationsObserver_ = null),
                            (this.observers_ = []),
                            (this.onTransitionEnd_ = this.onTransitionEnd_.bind(this)),
                            (this.refresh = (function (t, e) {
                                var n = !1,
                                    o = !1,
                                    r = 0;
                                function s() {
                                    n && ((n = !1), t()), o && c();
                                }
                                function a() {
                                    i(s);
                                }
                                function c() {
                                    var t = Date.now();
                                    if (n) {
                                        if (t - r < 2) return;
                                        o = !0;
                                    } else (n = !0), (o = !1), setTimeout(a, e);
                                    r = t;
                                }
                                return c;
                            })(this.refresh.bind(this), 20));
                    }
                    return (
                        (t.prototype.addObserver = function (t) {
                            ~this.observers_.indexOf(t) || this.observers_.push(t), this.connected_ || this.connect_();
                        }),
                        (t.prototype.removeObserver = function (t) {
                            var e = this.observers_,
                                n = e.indexOf(t);
                            ~n && e.splice(n, 1), !e.length && this.connected_ && this.disconnect_();
                        }),
                        (t.prototype.refresh = function () {
                            this.updateObservers_() && this.refresh();
                        }),
                        (t.prototype.updateObservers_ = function () {
                            var t = this.observers_.filter(function (t) {
                                return t.gatherActive(), t.hasActive();
                            });
                            return (
                                t.forEach(function (t) {
                                    return t.broadcastActive();
                                }),
                                t.length > 0
                            );
                        }),
                        (t.prototype.connect_ = function () {
                            o &&
                                !this.connected_ &&
                                (document.addEventListener("transitionend", this.onTransitionEnd_),
                                window.addEventListener("resize", this.refresh),
                                a
                                    ? ((this.mutationsObserver_ = new MutationObserver(this.refresh)), this.mutationsObserver_.observe(document, { attributes: !0, childList: !0, characterData: !0, subtree: !0 }))
                                    : (document.addEventListener("DOMSubtreeModified", this.refresh), (this.mutationEventsAdded_ = !0)),
                                (this.connected_ = !0));
                        }),
                        (t.prototype.disconnect_ = function () {
                            o &&
                                this.connected_ &&
                                (document.removeEventListener("transitionend", this.onTransitionEnd_),
                                window.removeEventListener("resize", this.refresh),
                                this.mutationsObserver_ && this.mutationsObserver_.disconnect(),
                                this.mutationEventsAdded_ && document.removeEventListener("DOMSubtreeModified", this.refresh),
                                (this.mutationsObserver_ = null),
                                (this.mutationEventsAdded_ = !1),
                                (this.connected_ = !1));
                        }),
                        (t.prototype.onTransitionEnd_ = function (t) {
                            var e = t.propertyName,
                                n = void 0 === e ? "" : e;
                            s.some(function (t) {
                                return !!~n.indexOf(t);
                            }) && this.refresh();
                        }),
                        (t.getInstance = function () {
                            return this.instance_ || (this.instance_ = new t()), this.instance_;
                        }),
                        (t.instance_ = null),
                        t
                    );
                })(),
                u = function (t, e) {
                    for (var n = 0, o = Object.keys(e); n < o.length; n++) {
                        var r = o[n];
                        Object.defineProperty(t, r, { value: e[r], enumerable: !1, writable: !1, configurable: !0 });
                    }
                    return t;
                },
                l = function (t) {
                    return (t && t.ownerDocument && t.ownerDocument.defaultView) || r;
                },
                d = m(0, 0, 0, 0);
            function p(t) {
                return parseFloat(t) || 0;
            }
            function h(t) {
                for (var e = [], n = 1; n < arguments.length; n++) e[n - 1] = arguments[n];
                return e.reduce(function (e, n) {
                    return e + p(t["border-" + n + "-width"]);
                }, 0);
            }
            function f(t) {
                var e = t.clientWidth,
                    n = t.clientHeight;
                if (!e && !n) return d;
                var o = l(t).getComputedStyle(t),
                    r = (function (t) {
                        for (var e = {}, n = 0, o = ["top", "right", "bottom", "left"]; n < o.length; n++) {
                            var r = o[n],
                                i = t["padding-" + r];
                            e[r] = p(i);
                        }
                        return e;
                    })(o),
                    i = r.left + r.right,
                    s = r.top + r.bottom,
                    a = p(o.width),
                    c = p(o.height);
                if (
                    ("border-box" === o.boxSizing && (Math.round(a + i) !== e && (a -= h(o, "left", "right") + i), Math.round(c + s) !== n && (c -= h(o, "top", "bottom") + s)),
                    !(function (t) {
                        return t === l(t).document.documentElement;
                    })(t))
                ) {
                    var u = Math.round(a + i) - e,
                        f = Math.round(c + s) - n;
                    1 !== Math.abs(u) && (a -= u), 1 !== Math.abs(f) && (c -= f);
                }
                return m(r.left, r.top, a, c);
            }
            var g =
                "undefined" != typeof SVGGraphicsElement
                    ? function (t) {
                          return t instanceof l(t).SVGGraphicsElement;
                      }
                    : function (t) {
                          return t instanceof l(t).SVGElement && "function" == typeof t.getBBox;
                      };
            function v(t) {
                return o
                    ? g(t)
                        ? (function (t) {
                              var e = t.getBBox();
                              return m(0, 0, e.width, e.height);
                          })(t)
                        : f(t)
                    : d;
            }
            function m(t, e, n, o) {
                return { x: t, y: e, width: n, height: o };
            }
            var y = (function () {
                    function t(t) {
                        (this.broadcastWidth = 0), (this.broadcastHeight = 0), (this.contentRect_ = m(0, 0, 0, 0)), (this.target = t);
                    }
                    return (
                        (t.prototype.isActive = function () {
                            var t = v(this.target);
                            return (this.contentRect_ = t), t.width !== this.broadcastWidth || t.height !== this.broadcastHeight;
                        }),
                        (t.prototype.broadcastRect = function () {
                            var t = this.contentRect_;
                            return (this.broadcastWidth = t.width), (this.broadcastHeight = t.height), t;
                        }),
                        t
                    );
                })(),
                w = function (t, e) {
                    var n,
                        o,
                        r,
                        i,
                        s,
                        a,
                        c,
                        l =
                            ((o = (n = e).x),
                            (r = n.y),
                            (i = n.width),
                            (s = n.height),
                            (a = "undefined" != typeof DOMRectReadOnly ? DOMRectReadOnly : Object),
                            (c = Object.create(a.prototype)),
                            u(c, { x: o, y: r, width: i, height: s, top: r, right: o + i, bottom: s + r, left: o }),
                            c);
                    u(this, { target: t, contentRect: l });
                },
                _ = (function () {
                    function t(t, e, o) {
                        if (((this.activeObservations_ = []), (this.observations_ = new n()), "function" != typeof t)) throw new TypeError("The callback provided as parameter 1 is not a function.");
                        (this.callback_ = t), (this.controller_ = e), (this.callbackCtx_ = o);
                    }
                    return (
                        (t.prototype.observe = function (t) {
                            if (!arguments.length) throw new TypeError("1 argument required, but only 0 present.");
                            if ("undefined" != typeof Element && Element instanceof Object) {
                                if (!(t instanceof l(t).Element)) throw new TypeError('parameter 1 is not of type "Element".');
                                var e = this.observations_;
                                e.has(t) || (e.set(t, new y(t)), this.controller_.addObserver(this), this.controller_.refresh());
                            }
                        }),
                        (t.prototype.unobserve = function (t) {
                            if (!arguments.length) throw new TypeError("1 argument required, but only 0 present.");
                            if ("undefined" != typeof Element && Element instanceof Object) {
                                if (!(t instanceof l(t).Element)) throw new TypeError('parameter 1 is not of type "Element".');
                                var e = this.observations_;
                                e.has(t) && (e.delete(t), e.size || this.controller_.removeObserver(this));
                            }
                        }),
                        (t.prototype.disconnect = function () {
                            this.clearActive(), this.observations_.clear(), this.controller_.removeObserver(this);
                        }),
                        (t.prototype.gatherActive = function () {
                            var t = this;
                            this.clearActive(),
                                this.observations_.forEach(function (e) {
                                    e.isActive() && t.activeObservations_.push(e);
                                });
                        }),
                        (t.prototype.broadcastActive = function () {
                            if (this.hasActive()) {
                                var t = this.callbackCtx_,
                                    e = this.activeObservations_.map(function (t) {
                                        return new w(t.target, t.broadcastRect());
                                    });
                                this.callback_.call(t, e, t), this.clearActive();
                            }
                        }),
                        (t.prototype.clearActive = function () {
                            this.activeObservations_.splice(0);
                        }),
                        (t.prototype.hasActive = function () {
                            return this.activeObservations_.length > 0;
                        }),
                        t
                    );
                })(),
                b = "undefined" != typeof WeakMap ? new WeakMap() : new n(),
                A = function t(e) {
                    if (!(this instanceof t)) throw new TypeError("Cannot call a class as a function.");
                    if (!arguments.length) throw new TypeError("1 argument required, but only 0 present.");
                    var n = c.getInstance(),
                        o = new _(e, n, this);
                    b.set(this, o);
                };
            ["observe", "unobserve", "disconnect"].forEach(function (t) {
                A.prototype[t] = function () {
                    var e;
                    return (e = b.get(this))[t].apply(e, arguments);
                };
            });
            var C = void 0 !== r.ResizeObserver ? r.ResizeObserver : A;
            e.a = C;
        }.call(this, n(5)));
    },
    function (t, e, n) {
        "use strict";
        (function (t) {
            var o = n(0),
                r = n(11),
                i = "object" == typeof exports && exports && !exports.nodeType && exports,
                s = i && "object" == typeof t && t && !t.nodeType && t,
                a = s && s.exports === i ? o.a.Buffer : void 0,
                c = (a ? a.isBuffer : void 0) || r.a;
            e.a = c;
        }.call(this, n(9)(t)));
    },
    function (t, e, n) {
        "use strict";
        (function (t) {
            var o = n(7),
                r = "object" == typeof exports && exports && !exports.nodeType && exports,
                i = r && "object" == typeof t && t && !t.nodeType && t,
                s = i && i.exports === r && o.a.process,
                a = (function () {
                    try {
                        var t = i && i.require && i.require("util").types;
                        return t || (s && s.binding && s.binding("util"));
                    } catch (t) {}
                })();
            e.a = a;
        }.call(this, n(9)(t)));
    },
    function (t, e) {
        var n;
        n = (function () {
            return this;
        })();
        try {
            n = n || new Function("return this")();
        } catch (t) {
            "object" == typeof window && (n = window);
        }
        t.exports = n;
    },
    function (t, e, n) {
        "use strict";
        e.a = function (t) {
            var e = this.constructor;
            return this.then(
                function (n) {
                    return e.resolve(t()).then(function () {
                        return n;
                    });
                },
                function (n) {
                    return e.resolve(t()).then(function () {
                        return e.reject(n);
                    });
                }
            );
        };
    },
    function (t, e, n) {
        "use strict";
        (function (t) {
            var n = "object" == typeof t && t && t.Object === Object && t;
            e.a = n;
        }.call(this, n(5)));
    },
    function (t, e, n) {
        "use strict";
        (function (t) {
            var o = n(0),
                r = "object" == typeof exports && exports && !exports.nodeType && exports,
                i = r && "object" == typeof t && t && !t.nodeType && t,
                s = i && i.exports === r ? o.a.Buffer : void 0,
                a = s ? s.allocUnsafe : void 0;
            e.a = function (t, e) {
                if (e) return t.slice();
                var n = t.length,
                    o = a ? a(n) : new t.constructor(n);
                return t.copy(o), o;
            };
        }.call(this, n(9)(t)));
    },
    function (t, e) {
        t.exports = function (t) {
            if (!t.webpackPolyfill) {
                var e = Object.create(t);
                e.children || (e.children = []),
                    Object.defineProperty(e, "loaded", {
                        enumerable: !0,
                        get: function () {
                            return e.l;
                        },
                    }),
                    Object.defineProperty(e, "id", {
                        enumerable: !0,
                        get: function () {
                            return e.i;
                        },
                    }),
                    Object.defineProperty(e, "exports", { enumerable: !0 }),
                    (e.webpackPolyfill = 1);
            }
            return e;
        };
    },
    function (t, e, n) {
        "use strict";
        (function (t) {
            var o = n(6),
                r = setTimeout;
            function i(t) {
                return Boolean(t && void 0 !== t.length);
            }
            function s() {}
            function a(t) {
                if (!(this instanceof a)) throw new TypeError("Promises must be constructed via new");
                if ("function" != typeof t) throw new TypeError("not a function");
                (this._state = 0), (this._handled = !1), (this._value = void 0), (this._deferreds = []), h(t, this);
            }
            function c(t, e) {
                for (; 3 === t._state; ) t = t._value;
                0 !== t._state
                    ? ((t._handled = !0),
                      a._immediateFn(function () {
                          var n = 1 === t._state ? e.onFulfilled : e.onRejected;
                          if (null !== n) {
                              var o;
                              try {
                                  o = n(t._value);
                              } catch (t) {
                                  return void l(e.promise, t);
                              }
                              u(e.promise, o);
                          } else (1 === t._state ? u : l)(e.promise, t._value);
                      }))
                    : t._deferreds.push(e);
            }
            function u(t, e) {
                try {
                    if (e === t) throw new TypeError("A promise cannot be resolved with itself.");
                    if (e && ("object" == typeof e || "function" == typeof e)) {
                        var n = e.then;
                        if (e instanceof a) return (t._state = 3), (t._value = e), void d(t);
                        if ("function" == typeof n)
                            return void h(
                                ((o = n),
                                (r = e),
                                function () {
                                    o.apply(r, arguments);
                                }),
                                t
                            );
                    }
                    (t._state = 1), (t._value = e), d(t);
                } catch (e) {
                    l(t, e);
                }
                var o, r;
            }
            function l(t, e) {
                (t._state = 2), (t._value = e), d(t);
            }
            function d(t) {
                2 === t._state &&
                    0 === t._deferreds.length &&
                    a._immediateFn(function () {
                        t._handled || a._unhandledRejectionFn(t._value);
                    });
                for (var e = 0, n = t._deferreds.length; e < n; e++) c(t, t._deferreds[e]);
                t._deferreds = null;
            }
            function p(t, e, n) {
                (this.onFulfilled = "function" == typeof t ? t : null), (this.onRejected = "function" == typeof e ? e : null), (this.promise = n);
            }
            function h(t, e) {
                var n = !1;
                try {
                    t(
                        function (t) {
                            n || ((n = !0), u(e, t));
                        },
                        function (t) {
                            n || ((n = !0), l(e, t));
                        }
                    );
                } catch (t) {
                    if (n) return;
                    (n = !0), l(e, t);
                }
            }
            (a.prototype.catch = function (t) {
                return this.then(null, t);
            }),
                (a.prototype.then = function (t, e) {
                    var n = new this.constructor(s);
                    return c(this, new p(t, e, n)), n;
                }),
                (a.prototype.finally = o.a),
                (a.all = function (t) {
                    return new a(function (e, n) {
                        if (!i(t)) return n(new TypeError("Promise.all accepts an array"));
                        var o = Array.prototype.slice.call(t);
                        if (0 === o.length) return e([]);
                        var r = o.length;
                        function s(t, i) {
                            try {
                                if (i && ("object" == typeof i || "function" == typeof i)) {
                                    var a = i.then;
                                    if ("function" == typeof a)
                                        return void a.call(
                                            i,
                                            function (e) {
                                                s(t, e);
                                            },
                                            n
                                        );
                                }
                                (o[t] = i), 0 == --r && e(o);
                            } catch (t) {
                                n(t);
                            }
                        }
                        for (var a = 0; a < o.length; a++) s(a, o[a]);
                    });
                }),
                (a.resolve = function (t) {
                    return t && "object" == typeof t && t.constructor === a
                        ? t
                        : new a(function (e) {
                              e(t);
                          });
                }),
                (a.reject = function (t) {
                    return new a(function (e, n) {
                        n(t);
                    });
                }),
                (a.race = function (t) {
                    return new a(function (e, n) {
                        if (!i(t)) return n(new TypeError("Promise.race accepts an array"));
                        for (var o = 0, r = t.length; o < r; o++) a.resolve(t[o]).then(e, n);
                    });
                }),
                (a._immediateFn =
                    ("function" == typeof t &&
                        function (e) {
                            t(e);
                        }) ||
                    function (t) {
                        r(t, 0);
                    }),
                (a._unhandledRejectionFn = function (t) {
                    "undefined" != typeof console && console && console.warn("Possible Unhandled Promise Rejection:", t);
                }),
                (e.a = a);
        }.call(this, n(16).setImmediate));
    },
    function (t, e, n) {
        "use strict";
        e.a = function () {
            return !1;
        };
    },
    function (t, e, n) {
        n(13), (t.exports = n(19));
    },
    function (t, e, n) {},
    function (t, e) {
        var n;
        (n = (function () {
            var t,
                e,
                n,
                o,
                r,
                i = [],
                s = i.concat,
                a = i.filter,
                c = i.slice,
                u = window.document,
                l = {},
                d = {},
                p = { "column-count": 1, columns: 1, "font-weight": 1, "line-height": 1, opacity: 1, "z-index": 1, zoom: 1 },
                h = /^\s*<(\w+|!)[^>]*>/,
                f = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
                g = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
                v = /^(?:body|html)$/i,
                m = /([A-Z])/g,
                y = ["val", "css", "html", "text", "data", "width", "height", "offset"],
                w = u.createElement("table"),
                _ = u.createElement("tr"),
                b = { tr: u.createElement("tbody"), tbody: w, thead: w, tfoot: w, td: _, th: _, "*": u.createElement("div") },
                A = /^[\w-]*$/,
                C = {},
                P = C.toString,
                E = {},
                k = u.createElement("div"),
                O = {
                    tabindex: "tabIndex",
                    readonly: "readOnly",
                    for: "htmlFor",
                    class: "className",
                    maxlength: "maxLength",
                    cellspacing: "cellSpacing",
                    cellpadding: "cellPadding",
                    rowspan: "rowSpan",
                    colspan: "colSpan",
                    usemap: "useMap",
                    frameborder: "frameBorder",
                    contenteditable: "contentEditable",
                },
                T =
                    Array.isArray ||
                    function (t) {
                        return t instanceof Array;
                    };
            function S(t) {
                return null == t ? String(t) : C[P.call(t)] || "object";
            }
            function D(t) {
                return "function" == S(t);
            }
            function F(t) {
                return null != t && t == t.window;
            }
            function L(t) {
                return null != t && t.nodeType == t.DOCUMENT_NODE;
            }
            function x(t) {
                return "object" == S(t);
            }
            function I(t) {
                return x(t) && !F(t) && Object.getPrototypeOf(t) == Object.prototype;
            }
            function j(t) {
                var n = !!t && "length" in t && t.length,
                    o = e.type(t);
                return "function" != o && !F(t) && ("array" == o || 0 === n || ("number" == typeof n && n > 0 && n - 1 in t));
            }
            function B(t) {
                return t
                    .replace(/::/g, "/")
                    .replace(/([A-Z]+)([A-Z][a-z])/g, "$1_$2")
                    .replace(/([a-z\d])([A-Z])/g, "$1_$2")
                    .replace(/_/g, "-")
                    .toLowerCase();
            }
            function $(t) {
                return t in d ? d[t] : (d[t] = new RegExp("(^|\\s)" + t + "(\\s|$)"));
            }
            function N(t, e) {
                return "number" != typeof e || p[B(t)] ? e : e + "px";
            }
            function H(t) {
                return "children" in t
                    ? c.call(t.children)
                    : e.map(t.childNodes, function (t) {
                          if (1 == t.nodeType) return t;
                      });
            }
            function M(t, e) {
                var n,
                    o = t ? t.length : 0;
                for (n = 0; n < o; n++) this[n] = t[n];
                (this.length = o), (this.selector = e || "");
            }
            function R(e, n, o) {
                for (t in n) o && (I(n[t]) || T(n[t])) ? (I(n[t]) && !I(e[t]) && (e[t] = {}), T(n[t]) && !T(e[t]) && (e[t] = []), R(e[t], n[t], o)) : void 0 !== n[t] && (e[t] = n[t]);
            }
            function W(t, n) {
                return null == n ? e(t) : e(t).filter(n);
            }
            function G(t, e, n, o) {
                return D(e) ? e.call(t, n, o) : e;
            }
            function z(t, e, n) {
                null == n ? t.removeAttribute(e) : t.setAttribute(e, n);
            }
            function V(t, e) {
                var n = t.className || "",
                    o = n && void 0 !== n.baseVal;
                if (void 0 === e) return o ? n.baseVal : n;
                o ? (n.baseVal = e) : (t.className = e);
            }
            function U(t) {
                try {
                    return t ? "true" == t || ("false" != t && ("null" == t ? null : +t + "" == t ? +t : /^[\[\{]/.test(t) ? e.parseJSON(t) : t)) : t;
                } catch (e) {
                    return t;
                }
            }
            function Z(t, e) {
                e(t);
                for (var n = 0, o = t.childNodes.length; n < o; n++) Z(t.childNodes[n], e);
            }
            return (
                (E.matches = function (t, e) {
                    if (!e || !t || 1 !== t.nodeType) return !1;
                    var n = t.matches || t.webkitMatchesSelector || t.mozMatchesSelector || t.oMatchesSelector || t.matchesSelector;
                    if (n) return n.call(t, e);
                    var o,
                        r = t.parentNode,
                        i = !r;
                    return i && (r = k).appendChild(t), (o = ~E.qsa(r, e).indexOf(t)), i && k.removeChild(t), o;
                }),
                (o = function (t) {
                    return t.replace(/-+(.)?/g, function (t, e) {
                        return e ? e.toUpperCase() : "";
                    });
                }),
                (r = function (t) {
                    return a.call(t, function (e, n) {
                        return t.indexOf(e) == n;
                    });
                }),
                (E.fragment = function (t, n, o) {
                    var r, i, s;
                    return (
                        f.test(t) && (r = e(u.createElement(RegExp.$1))),
                        r ||
                            (t.replace && (t = t.replace(g, "<$1><$2>")),
                            void 0 === n && (n = h.test(t) && RegExp.$1),
                            n in b || (n = "*"),
                            ((s = b[n]).innerHTML = "" + t),
                            (r = e.each(c.call(s.childNodes), function () {
                                s.removeChild(this);
                            }))),
                        I(o) &&
                            ((i = e(r)),
                            e.each(o, function (t, e) {
                                y.indexOf(t) > -1 ? i[t](e) : i.attr(t, e);
                            })),
                        r
                    );
                }),
                (E.Z = function (t, e) {
                    return new M(t, e);
                }),
                (E.isZ = function (t) {
                    return t instanceof E.Z;
                }),
                (E.init = function (t, n) {
                    var o, r;
                    if (!t) return E.Z();
                    if ("string" == typeof t)
                        if ("<" == (t = t.trim())[0] && h.test(t)) (o = E.fragment(t, RegExp.$1, n)), (t = null);
                        else {
                            if (void 0 !== n) return e(n).find(t);
                            o = E.qsa(u, t);
                        }
                    else {
                        if (D(t)) return e(u).ready(t);
                        if (E.isZ(t)) return t;
                        if (T(t))
                            (r = t),
                                (o = a.call(r, function (t) {
                                    return null != t;
                                }));
                        else if (x(t)) (o = [t]), (t = null);
                        else if (h.test(t)) (o = E.fragment(t.trim(), RegExp.$1, n)), (t = null);
                        else {
                            if (void 0 !== n) return e(n).find(t);
                            o = E.qsa(u, t);
                        }
                    }
                    return E.Z(o, t);
                }),
                ((e = function (t, e) {
                    return E.init(t, e);
                }).extend = function (t) {
                    var e,
                        n = c.call(arguments, 1);
                    return (
                        "boolean" == typeof t && ((e = t), (t = n.shift())),
                        n.forEach(function (n) {
                            R(t, n, e);
                        }),
                        t
                    );
                }),
                (E.qsa = function (t, e) {
                    var n,
                        o = "#" == e[0],
                        r = !o && "." == e[0],
                        i = o || r ? e.slice(1) : e,
                        s = A.test(i);
                    return t.getElementById && s && o
                        ? (n = t.getElementById(i))
                            ? [n]
                            : []
                        : 1 !== t.nodeType && 9 !== t.nodeType && 11 !== t.nodeType
                        ? []
                        : c.call(s && !o && t.getElementsByClassName ? (r ? t.getElementsByClassName(i) : t.getElementsByTagName(e)) : t.querySelectorAll(e));
                }),
                (e.contains = u.documentElement.contains
                    ? function (t, e) {
                          return t !== e && t.contains(e);
                      }
                    : function (t, e) {
                          for (; e && (e = e.parentNode); ) if (e === t) return !0;
                          return !1;
                      }),
                (e.type = S),
                (e.isFunction = D),
                (e.isWindow = F),
                (e.isArray = T),
                (e.isPlainObject = I),
                (e.isEmptyObject = function (t) {
                    var e;
                    for (e in t) return !1;
                    return !0;
                }),
                (e.isNumeric = function (t) {
                    var e = Number(t),
                        n = typeof t;
                    return (null != t && "boolean" != n && ("string" != n || t.length) && !isNaN(e) && isFinite(e)) || !1;
                }),
                (e.inArray = function (t, e, n) {
                    return i.indexOf.call(e, t, n);
                }),
                (e.camelCase = o),
                (e.trim = function (t) {
                    return null == t ? "" : String.prototype.trim.call(t);
                }),
                (e.uuid = 0),
                (e.support = {}),
                (e.expr = {}),
                (e.noop = function () {}),
                (e.map = function (t, n) {
                    var o,
                        r,
                        i,
                        s,
                        a = [];
                    if (j(t)) for (r = 0; r < t.length; r++) null != (o = n(t[r], r)) && a.push(o);
                    else for (i in t) null != (o = n(t[i], i)) && a.push(o);
                    return (s = a).length > 0 ? e.fn.concat.apply([], s) : s;
                }),
                (e.each = function (t, e) {
                    var n, o;
                    if (j(t)) {
                        for (n = 0; n < t.length; n++) if (!1 === e.call(t[n], n, t[n])) return t;
                    } else for (o in t) if (!1 === e.call(t[o], o, t[o])) return t;
                    return t;
                }),
                (e.grep = function (t, e) {
                    return a.call(t, e);
                }),
                window.JSON && (e.parseJSON = JSON.parse),
                e.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function (t, e) {
                    C["[object " + e + "]"] = e.toLowerCase();
                }),
                (e.fn = {
                    constructor: E.Z,
                    length: 0,
                    forEach: i.forEach,
                    reduce: i.reduce,
                    push: i.push,
                    sort: i.sort,
                    splice: i.splice,
                    indexOf: i.indexOf,
                    concat: function () {
                        var t,
                            e,
                            n = [];
                        for (t = 0; t < arguments.length; t++) (e = arguments[t]), (n[t] = E.isZ(e) ? e.toArray() : e);
                        return s.apply(E.isZ(this) ? this.toArray() : this, n);
                    },
                    map: function (t) {
                        return e(
                            e.map(this, function (e, n) {
                                return t.call(e, n, e);
                            })
                        );
                    },
                    slice: function () {
                        return e(c.apply(this, arguments));
                    },
                    ready: function (t) {
                        if ("complete" === u.readyState || ("loading" !== u.readyState && !u.documentElement.doScroll))
                            setTimeout(function () {
                                t(e);
                            }, 0);
                        else {
                            var n = function () {
                                u.removeEventListener("DOMContentLoaded", n, !1), window.removeEventListener("load", n, !1), t(e);
                            };
                            u.addEventListener("DOMContentLoaded", n, !1), window.addEventListener("load", n, !1);
                        }
                        return this;
                    },
                    get: function (t) {
                        return void 0 === t ? c.call(this) : this[t >= 0 ? t : t + this.length];
                    },
                    toArray: function () {
                        return this.get();
                    },
                    size: function () {
                        return this.length;
                    },
                    remove: function () {
                        return this.each(function () {
                            null != this.parentNode && this.parentNode.removeChild(this);
                        });
                    },
                    each: function (t) {
                        return (
                            i.every.call(this, function (e, n) {
                                return !1 !== t.call(e, n, e);
                            }),
                            this
                        );
                    },
                    filter: function (t) {
                        return D(t)
                            ? this.not(this.not(t))
                            : e(
                                  a.call(this, function (e) {
                                      return E.matches(e, t);
                                  })
                              );
                    },
                    add: function (t, n) {
                        return e(r(this.concat(e(t, n))));
                    },
                    is: function (t) {
                        return "string" == typeof t ? this.length > 0 && E.matches(this[0], t) : t && this.selector == t.selector;
                    },
                    not: function (t) {
                        var n = [];
                        if (D(t) && void 0 !== t.call)
                            this.each(function (e) {
                                t.call(this, e) || n.push(this);
                            });
                        else {
                            var o = "string" == typeof t ? this.filter(t) : j(t) && D(t.item) ? c.call(t) : e(t);
                            this.forEach(function (t) {
                                o.indexOf(t) < 0 && n.push(t);
                            });
                        }
                        return e(n);
                    },
                    has: function (t) {
                        return this.filter(function () {
                            return x(t) ? e.contains(this, t) : e(this).find(t).size();
                        });
                    },
                    eq: function (t) {
                        return -1 === t ? this.slice(t) : this.slice(t, +t + 1);
                    },
                    first: function () {
                        var t = this[0];
                        return t && !x(t) ? t : e(t);
                    },
                    last: function () {
                        var t = this[this.length - 1];
                        return t && !x(t) ? t : e(t);
                    },
                    find: function (t) {
                        var n = this;
                        return t
                            ? "object" == typeof t
                                ? e(t).filter(function () {
                                      var t = this;
                                      return i.some.call(n, function (n) {
                                          return e.contains(n, t);
                                      });
                                  })
                                : 1 == this.length
                                ? e(E.qsa(this[0], t))
                                : this.map(function () {
                                      return E.qsa(this, t);
                                  })
                            : e();
                    },
                    closest: function (t, n) {
                        var o = [],
                            r = "object" == typeof t && e(t);
                        return (
                            this.each(function (e, i) {
                                for (; i && !(r ? r.indexOf(i) >= 0 : E.matches(i, t)); ) i = i !== n && !L(i) && i.parentNode;
                                i && o.indexOf(i) < 0 && o.push(i);
                            }),
                            e(o)
                        );
                    },
                    parents: function (t) {
                        for (var n = [], o = this; o.length > 0; )
                            o = e.map(o, function (t) {
                                if ((t = t.parentNode) && !L(t) && n.indexOf(t) < 0) return n.push(t), t;
                            });
                        return W(n, t);
                    },
                    parent: function (t) {
                        return W(r(this.pluck("parentNode")), t);
                    },
                    children: function (t) {
                        return W(
                            this.map(function () {
                                return H(this);
                            }),
                            t
                        );
                    },
                    contents: function () {
                        return this.map(function () {
                            return this.contentDocument || c.call(this.childNodes);
                        });
                    },
                    siblings: function (t) {
                        return W(
                            this.map(function (t, e) {
                                return a.call(H(e.parentNode), function (t) {
                                    return t !== e;
                                });
                            }),
                            t
                        );
                    },
                    empty: function () {
                        return this.each(function () {
                            this.innerHTML = "";
                        });
                    },
                    pluck: function (t) {
                        return e.map(this, function (e) {
                            return e[t];
                        });
                    },
                    show: function () {
                        return this.each(function () {
                            var t, e, n;
                            "none" == this.style.display && (this.style.display = ""),
                                "none" == getComputedStyle(this, "").getPropertyValue("display") &&
                                    (this.style.display =
                                        ((t = this.nodeName),
                                        l[t] || ((e = u.createElement(t)), u.body.appendChild(e), (n = getComputedStyle(e, "").getPropertyValue("display")), e.parentNode.removeChild(e), "none" == n && (n = "block"), (l[t] = n)),
                                        l[t]));
                        });
                    },
                    replaceWith: function (t) {
                        return this.before(t).remove();
                    },
                    wrap: function (t) {
                        var n = D(t);
                        if (this[0] && !n)
                            var o = e(t).get(0),
                                r = o.parentNode || this.length > 1;
                        return this.each(function (i) {
                            e(this).wrapAll(n ? t.call(this, i) : r ? o.cloneNode(!0) : o);
                        });
                    },
                    wrapAll: function (t) {
                        if (this[0]) {
                            var n;
                            for (e(this[0]).before((t = e(t))); (n = t.children()).length; ) t = n.first();
                            e(t).append(this);
                        }
                        return this;
                    },
                    wrapInner: function (t) {
                        var n = D(t);
                        return this.each(function (o) {
                            var r = e(this),
                                i = r.contents(),
                                s = n ? t.call(this, o) : t;
                            i.length ? i.wrapAll(s) : r.append(s);
                        });
                    },
                    unwrap: function () {
                        return (
                            this.parent().each(function () {
                                e(this).replaceWith(e(this).children());
                            }),
                            this
                        );
                    },
                    clone: function () {
                        return this.map(function () {
                            return this.cloneNode(!0);
                        });
                    },
                    hide: function () {
                        return this.css("display", "none");
                    },
                    toggle: function (t) {
                        return this.each(function () {
                            var n = e(this);
                            (void 0 === t ? "none" == n.css("display") : t) ? n.show() : n.hide();
                        });
                    },
                    prev: function (t) {
                        return e(this.pluck("previousElementSibling")).filter(t || "*");
                    },
                    next: function (t) {
                        return e(this.pluck("nextElementSibling")).filter(t || "*");
                    },
                    html: function (t) {
                        return 0 in arguments
                            ? this.each(function (n) {
                                  var o = this.innerHTML;
                                  e(this).empty().append(G(this, t, n, o));
                              })
                            : 0 in this
                            ? this[0].innerHTML
                            : null;
                    },
                    text: function (t) {
                        return 0 in arguments
                            ? this.each(function (e) {
                                  var n = G(this, t, e, this.textContent);
                                  this.textContent = null == n ? "" : "" + n;
                              })
                            : 0 in this
                            ? this.pluck("textContent").join("")
                            : null;
                    },
                    attr: function (e, n) {
                        var o;
                        return "string" != typeof e || 1 in arguments
                            ? this.each(function (o) {
                                  if (1 === this.nodeType)
                                      if (x(e)) for (t in e) z(this, t, e[t]);
                                      else z(this, e, G(this, n, o, this.getAttribute(e)));
                              })
                            : 0 in this && 1 == this[0].nodeType && null != (o = this[0].getAttribute(e))
                            ? o
                            : void 0;
                    },
                    removeAttr: function (t) {
                        return this.each(function () {
                            1 === this.nodeType &&
                                t.split(" ").forEach(function (t) {
                                    z(this, t);
                                }, this);
                        });
                    },
                    prop: function (e, n) {
                        return "string" != typeof (e = O[e] || e) || 1 in arguments
                            ? this.each(function (o) {
                                  if (x(e)) for (t in e) this[O[t] || t] = e[t];
                                  else this[e] = G(this, n, o, this[e]);
                              })
                            : this[0] && this[0][e];
                    },
                    removeProp: function (t) {
                        return (
                            (t = O[t] || t),
                            this.each(function () {
                                delete this[t];
                            })
                        );
                    },
                    data: function (t, e) {
                        var n = "data-" + t.replace(m, "-$1").toLowerCase(),
                            o = 1 in arguments ? this.attr(n, e) : this.attr(n);
                        return null !== o ? U(o) : void 0;
                    },
                    val: function (t) {
                        return 0 in arguments
                            ? (null == t && (t = ""),
                              this.each(function (e) {
                                  this.value = G(this, t, e, this.value);
                              }))
                            : this[0] &&
                                  (this[0].multiple
                                      ? e(this[0])
                                            .find("option")
                                            .filter(function () {
                                                return this.selected;
                                            })
                                            .pluck("value")
                                      : this[0].value);
                    },
                    offset: function (t) {
                        if (t)
                            return this.each(function (n) {
                                var o = e(this),
                                    r = G(this, t, n, o.offset()),
                                    i = o.offsetParent().offset(),
                                    s = { top: r.top - i.top, left: r.left - i.left };
                                "static" == o.css("position") && (s.position = "relative"), o.css(s);
                            });
                        if (!this.length) return null;
                        if (u.documentElement !== this[0] && !e.contains(u.documentElement, this[0])) return { top: 0, left: 0 };
                        var n = this[0].getBoundingClientRect();
                        return { left: n.left + window.pageXOffset, top: n.top + window.pageYOffset, width: Math.round(n.width), height: Math.round(n.height) };
                    },
                    css: function (n, r) {
                        if (arguments.length < 2) {
                            var i = this[0];
                            if ("string" == typeof n) {
                                if (!i) return;
                                return i.style[o(n)] || getComputedStyle(i, "").getPropertyValue(n);
                            }
                            if (T(n)) {
                                if (!i) return;
                                var s = {},
                                    a = getComputedStyle(i, "");
                                return (
                                    e.each(n, function (t, e) {
                                        s[e] = i.style[o(e)] || a.getPropertyValue(e);
                                    }),
                                    s
                                );
                            }
                        }
                        var c = "";
                        if ("string" == S(n))
                            r || 0 === r
                                ? (c = B(n) + ":" + N(n, r))
                                : this.each(function () {
                                      this.style.removeProperty(B(n));
                                  });
                        else
                            for (t in n)
                                n[t] || 0 === n[t]
                                    ? (c += B(t) + ":" + N(t, n[t]) + ";")
                                    : this.each(function () {
                                          this.style.removeProperty(B(t));
                                      });
                        return this.each(function () {
                            this.style.cssText += ";" + c;
                        });
                    },
                    index: function (t) {
                        return t ? this.indexOf(e(t)[0]) : this.parent().children().indexOf(this[0]);
                    },
                    hasClass: function (t) {
                        return (
                            !!t &&
                            i.some.call(
                                this,
                                function (t) {
                                    return this.test(V(t));
                                },
                                $(t)
                            )
                        );
                    },
                    addClass: function (t) {
                        return t
                            ? this.each(function (o) {
                                  if ("className" in this) {
                                      n = [];
                                      var r = V(this);
                                      G(this, t, o, r)
                                          .split(/\s+/g)
                                          .forEach(function (t) {
                                              e(this).hasClass(t) || n.push(t);
                                          }, this),
                                          n.length && V(this, r + (r ? " " : "") + n.join(" "));
                                  }
                              })
                            : this;
                    },
                    removeClass: function (t) {
                        return this.each(function (e) {
                            if ("className" in this) {
                                if (void 0 === t) return V(this, "");
                                (n = V(this)),
                                    G(this, t, e, n)
                                        .split(/\s+/g)
                                        .forEach(function (t) {
                                            n = n.replace($(t), " ");
                                        }),
                                    V(this, n.trim());
                            }
                        });
                    },
                    toggleClass: function (t, n) {
                        return t
                            ? this.each(function (o) {
                                  var r = e(this);
                                  G(this, t, o, V(this))
                                      .split(/\s+/g)
                                      .forEach(function (t) {
                                          (void 0 === n ? !r.hasClass(t) : n) ? r.addClass(t) : r.removeClass(t);
                                      });
                              })
                            : this;
                    },
                    scrollTop: function (t) {
                        if (this.length) {
                            var e = "scrollTop" in this[0];
                            return void 0 === t
                                ? e
                                    ? this[0].scrollTop
                                    : this[0].pageYOffset
                                : this.each(
                                      e
                                          ? function () {
                                                this.scrollTop = t;
                                            }
                                          : function () {
                                                this.scrollTo(this.scrollX, t);
                                            }
                                  );
                        }
                    },
                    scrollLeft: function (t) {
                        if (this.length) {
                            var e = "scrollLeft" in this[0];
                            return void 0 === t
                                ? e
                                    ? this[0].scrollLeft
                                    : this[0].pageXOffset
                                : this.each(
                                      e
                                          ? function () {
                                                this.scrollLeft = t;
                                            }
                                          : function () {
                                                this.scrollTo(t, this.scrollY);
                                            }
                                  );
                        }
                    },
                    position: function () {
                        if (this.length) {
                            var t = this[0],
                                n = this.offsetParent(),
                                o = this.offset(),
                                r = v.test(n[0].nodeName) ? { top: 0, left: 0 } : n.offset();
                            return (
                                (o.top -= parseFloat(e(t).css("margin-top")) || 0),
                                (o.left -= parseFloat(e(t).css("margin-left")) || 0),
                                (r.top += parseFloat(e(n[0]).css("border-top-width")) || 0),
                                (r.left += parseFloat(e(n[0]).css("border-left-width")) || 0),
                                { top: o.top - r.top, left: o.left - r.left }
                            );
                        }
                    },
                    offsetParent: function () {
                        return this.map(function () {
                            for (var t = this.offsetParent || u.body; t && !v.test(t.nodeName) && "static" == e(t).css("position"); ) t = t.offsetParent;
                            return t;
                        });
                    },
                }),
                (e.fn.detach = e.fn.remove),
                ["width", "height"].forEach(function (t) {
                    var n = t.replace(/./, function (t) {
                        return t[0].toUpperCase();
                    });
                    e.fn[t] = function (o) {
                        var r,
                            i = this[0];
                        return void 0 === o
                            ? F(i)
                                ? i["inner" + n]
                                : L(i)
                                ? i.documentElement["scroll" + n]
                                : (r = this.offset()) && r[t]
                            : this.each(function (n) {
                                  (i = e(this)).css(t, G(this, o, n, i[t]()));
                              });
                    };
                }),
                ["after", "prepend", "before", "append"].forEach(function (t, n) {
                    var o = n % 2;
                    (e.fn[t] = function () {
                        var t,
                            r,
                            i = e.map(arguments, function (n) {
                                var o = [];
                                return "array" == (t = S(n))
                                    ? (n.forEach(function (t) {
                                          return void 0 !== t.nodeType ? o.push(t) : e.zepto.isZ(t) ? (o = o.concat(t.get())) : void (o = o.concat(E.fragment(t)));
                                      }),
                                      o)
                                    : "object" == t || null == n
                                    ? n
                                    : E.fragment(n);
                            }),
                            s = this.length > 1;
                        return i.length < 1
                            ? this
                            : this.each(function (t, a) {
                                  (r = o ? a : a.parentNode), (a = 0 == n ? a.nextSibling : 1 == n ? a.firstChild : 2 == n ? a : null);
                                  var c = e.contains(u.documentElement, r);
                                  i.forEach(function (t) {
                                      if (s) t = t.cloneNode(!0);
                                      else if (!r) return e(t).remove();
                                      r.insertBefore(t, a),
                                          c &&
                                              Z(t, function (t) {
                                                  if (!(null == t.nodeName || "SCRIPT" !== t.nodeName.toUpperCase() || (t.type && "text/javascript" !== t.type) || t.src)) {
                                                      var e = t.ownerDocument ? t.ownerDocument.defaultView : window;
                                                      e.eval.call(e, t.innerHTML);
                                                  }
                                              });
                                  });
                              });
                    }),
                        (e.fn[o ? t + "To" : "insert" + (n ? "Before" : "After")] = function (n) {
                            return e(n)[t](this), this;
                        });
                }),
                (E.Z.prototype = M.prototype = e.fn),
                (E.uniq = r),
                (E.deserializeValue = U),
                (e.zepto = E),
                e
            );
        })()),
            (window.$crtZepto = n),
            (window.CuratorZepto = n),
            (function (t) {
                var e = 1,
                    n = Array.prototype.slice,
                    o = t.isFunction,
                    r = function (t) {
                        return "string" == typeof t;
                    },
                    i = {},
                    s = {},
                    a = "onfocusin" in window,
                    c = { focus: "focusin", blur: "focusout" },
                    u = { mouseenter: "mouseover", mouseleave: "mouseout" };
                function l(t) {
                    return t._zid || (t._zid = e++);
                }
                function d(t, e, n, o) {
                    if ((e = p(e)).ns) var r = ((s = e.ns), new RegExp("(?:^| )" + s.replace(" ", " .* ?") + "(?: |$)"));
                    var s;
                    return (i[l(t)] || []).filter(function (t) {
                        return t && (!e.e || t.e == e.e) && (!e.ns || r.test(t.ns)) && (!n || l(t.fn) === l(n)) && (!o || t.sel == o);
                    });
                }
                function p(t) {
                    var e = ("" + t).split(".");
                    return { e: e[0], ns: e.slice(1).sort().join(" ") };
                }
                function h(t, e) {
                    return (t.del && !a && t.e in c) || !!e;
                }
                function f(t) {
                    return u[t] || (a && c[t]) || t;
                }
                function g(e, n, o, r, s, a, c) {
                    var d = l(e),
                        g = i[d] || (i[d] = []);
                    n.split(/\s/).forEach(function (n) {
                        if ("ready" == n) return t(document).ready(o);
                        var i = p(n);
                        (i.fn = o),
                            (i.sel = s),
                            i.e in u &&
                                (o = function (e) {
                                    var n = e.relatedTarget;
                                    if (!n || (n !== this && !t.contains(this, n))) return i.fn.apply(this, arguments);
                                }),
                            (i.del = a);
                        var l = a || o;
                        (i.proxy = function (t) {
                            if (!(t = b(t)).isImmediatePropagationStopped()) {
                                t.data = r;
                                var n = l.apply(e, null == t._args ? [t] : [t].concat(t._args));
                                return !1 === n && (t.preventDefault(), t.stopPropagation()), n;
                            }
                        }),
                            (i.i = g.length),
                            g.push(i),
                            "addEventListener" in e && e.addEventListener(f(i.e), i.proxy, h(i, c));
                    });
                }
                function v(t, e, n, o, r) {
                    var s = l(t);
                    (e || "").split(/\s/).forEach(function (e) {
                        d(t, e, n, o).forEach(function (e) {
                            delete i[s][e.i], "removeEventListener" in t && t.removeEventListener(f(e.e), e.proxy, h(e, r));
                        });
                    });
                }
                (s.click = s.mousedown = s.mouseup = s.mousemove = "MouseEvents"),
                    (t.event = { add: g, remove: v }),
                    (t.proxy = function (e, i) {
                        var s = 2 in arguments && n.call(arguments, 2);
                        if (o(e)) {
                            var a = function () {
                                return e.apply(i, s ? s.concat(n.call(arguments)) : arguments);
                            };
                            return (a._zid = l(e)), a;
                        }
                        if (r(i)) return s ? (s.unshift(e[i], e), t.proxy.apply(null, s)) : t.proxy(e[i], e);
                        throw new TypeError("expected function");
                    }),
                    (t.fn.bind = function (t, e, n) {
                        return this.on(t, e, n);
                    }),
                    (t.fn.unbind = function (t, e) {
                        return this.off(t, e);
                    }),
                    (t.fn.one = function (t, e, n, o) {
                        return this.on(t, e, n, o, 1);
                    });
                var m = function () {
                        return !0;
                    },
                    y = function () {
                        return !1;
                    },
                    w = /^([A-Z]|returnValue$|layer[XY]$|webkitMovement[XY]$)/,
                    _ = { preventDefault: "isDefaultPrevented", stopImmediatePropagation: "isImmediatePropagationStopped", stopPropagation: "isPropagationStopped" };
                function b(e, n) {
                    if (n || !e.isDefaultPrevented) {
                        n || (n = e),
                            t.each(_, function (t, o) {
                                var r = n[t];
                                (e[t] = function () {
                                    return (this[o] = m), r && r.apply(n, arguments);
                                }),
                                    (e[o] = y);
                            });
                        try {
                            e.timeStamp || (e.timeStamp = Date.now());
                        } catch (t) {}
                        (void 0 !== n.defaultPrevented ? n.defaultPrevented : "returnValue" in n ? !1 === n.returnValue : n.getPreventDefault && n.getPreventDefault()) && (e.isDefaultPrevented = m);
                    }
                    return e;
                }
                function A(t) {
                    var e,
                        n = { originalEvent: t };
                    for (e in t) w.test(e) || void 0 === t[e] || (n[e] = t[e]);
                    return b(n, t);
                }
                (t.fn.delegate = function (t, e, n) {
                    return this.on(e, t, n);
                }),
                    (t.fn.undelegate = function (t, e, n) {
                        return this.off(e, t, n);
                    }),
                    (t.fn.live = function (e, n) {
                        return t(document.body).delegate(this.selector, e, n), this;
                    }),
                    (t.fn.die = function (e, n) {
                        return t(document.body).undelegate(this.selector, e, n), this;
                    }),
                    (t.fn.on = function (e, i, s, a, c) {
                        var u,
                            l,
                            d = this;
                        return e && !r(e)
                            ? (t.each(e, function (t, e) {
                                  d.on(t, i, s, e, c);
                              }),
                              d)
                            : (r(i) || o(a) || !1 === a || ((a = s), (s = i), (i = void 0)),
                              (void 0 !== a && !1 !== s) || ((a = s), (s = void 0)),
                              !1 === a && (a = y),
                              d.each(function (o, r) {
                                  c &&
                                      (u = function (t) {
                                          return v(r, t.type, a), a.apply(this, arguments);
                                      }),
                                      i &&
                                          (l = function (e) {
                                              var o,
                                                  s = t(e.target).closest(i, r).get(0);
                                              if (s && s !== r) return (o = t.extend(A(e), { currentTarget: s, liveFired: r })), (u || a).apply(s, [o].concat(n.call(arguments, 1)));
                                          }),
                                      g(r, e, a, s, i, l || u);
                              }));
                    }),
                    (t.fn.off = function (e, n, i) {
                        var s = this;
                        return e && !r(e)
                            ? (t.each(e, function (t, e) {
                                  s.off(t, n, e);
                              }),
                              s)
                            : (r(n) || o(i) || !1 === i || ((i = n), (n = void 0)),
                              !1 === i && (i = y),
                              s.each(function () {
                                  v(this, e, i, n);
                              }));
                    }),
                    (t.fn.trigger = function (e, n) {
                        return (
                            ((e = r(e) || t.isPlainObject(e) ? t.Event(e) : b(e))._args = n),
                            this.each(function () {
                                e.type in c && "function" == typeof this[e.type] ? this[e.type]() : "dispatchEvent" in this ? this.dispatchEvent(e) : t(this).triggerHandler(e, n);
                            })
                        );
                    }),
                    (t.fn.triggerHandler = function (e, n) {
                        var o, i;
                        return (
                            this.each(function (s, a) {
                                ((o = A(r(e) ? t.Event(e) : e))._args = n),
                                    (o.target = a),
                                    t.each(d(a, e.type || e), function (t, e) {
                                        if (((i = e.proxy(o)), o.isImmediatePropagationStopped())) return !1;
                                    });
                            }),
                            i
                        );
                    }),
                    "focusin focusout focus blur load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select keydown keypress keyup error".split(" ").forEach(function (e) {
                        t.fn[e] = function (t) {
                            return 0 in arguments ? this.bind(e, t) : this.trigger(e);
                        };
                    }),
                    (t.Event = function (t, e) {
                        r(t) || (t = (e = t).type);
                        var n = document.createEvent(s[t] || "Events"),
                            o = !0;
                        if (e) for (var i in e) "bubbles" == i ? (o = !!e[i]) : (n[i] = e[i]);
                        return n.initEvent(t, o, !0), b(n);
                    });
            })(n),
            (function () {
                try {
                    getComputedStyle(void 0);
                } catch (e) {
                    var t = getComputedStyle;
                    window.getComputedStyle = function (e, n) {
                        try {
                            return t(e, n);
                        } catch (t) {
                            return null;
                        }
                    };
                }
            })(),
            (function (t, e) {
                var n,
                    o,
                    r,
                    i,
                    s,
                    a,
                    c,
                    u,
                    l,
                    d,
                    p = "",
                    h = document.createElement("div"),
                    f = /^((translate|rotate|scale)(X|Y|Z|3d)?|matrix(3d)?|perspective|skew(X|Y)?)$/i,
                    g = {};
                function v(t) {
                    return n ? n + t : t.toLowerCase();
                }
                void 0 === h.style.transform &&
                    t.each({ Webkit: "webkit", Moz: "", O: "o" }, function (t, e) {
                        if (void 0 !== h.style[t + "TransitionProperty"]) return (p = "-" + t.toLowerCase() + "-"), (n = e), !1;
                    }),
                    (o = p + "transform"),
                    (g[(r = p + "transition-property")] = g[(i = p + "transition-duration")] = g[(a = p + "transition-delay")] = g[(s = p + "transition-timing-function")] = g[(c = p + "animation-name")] = g[
                        (u = p + "animation-duration")
                    ] = g[(d = p + "animation-delay")] = g[(l = p + "animation-timing-function")] = ""),
                    (t.fx = { off: void 0 === n && void 0 === h.style.transitionProperty, speeds: { _default: 400, fast: 200, slow: 600 }, cssPrefix: p, transitionEnd: v("TransitionEnd"), animationEnd: v("AnimationEnd") }),
                    (t.fn.animate = function (e, n, o, r, i) {
                        return (
                            t.isFunction(n) && ((r = n), (o = void 0), (n = void 0)),
                            t.isFunction(o) && ((r = o), (o = void 0)),
                            t.isPlainObject(n) && ((o = n.easing), (r = n.complete), (i = n.delay), (n = n.duration)),
                            n && (n = ("number" == typeof n ? n : t.fx.speeds[n] || t.fx.speeds._default) / 1e3),
                            i && (i = parseFloat(i) / 1e3),
                            this.anim(e, n, o, r, i)
                        );
                    }),
                    (t.fn.anim = function (e, n, p, h, v) {
                        var m,
                            y,
                            w,
                            _ = {},
                            b = "",
                            A = this,
                            C = t.fx.transitionEnd,
                            P = !1;
                        if ((void 0 === n && (n = t.fx.speeds._default / 1e3), void 0 === v && (v = 0), t.fx.off && (n = 0), "string" == typeof e))
                            (_[c] = e), (_[u] = n + "s"), (_[d] = v + "s"), (_[l] = p || "linear"), (C = t.fx.animationEnd);
                        else {
                            for (m in ((y = []), e)) f.test(m) ? (b += m + "(" + e[m] + ") ") : ((_[m] = e[m]), y.push(m.replace(/([A-Z])/g, "-$1").toLowerCase()));
                            b && ((_[o] = b), y.push(o)), n > 0 && "object" == typeof e && ((_[r] = y.join(", ")), (_[i] = n + "s"), (_[a] = v + "s"), (_[s] = p || "linear"));
                        }
                        return (
                            (w = function (e) {
                                if (void 0 !== e) {
                                    if (e.target !== e.currentTarget) return;
                                    t(e.target).unbind(C, w);
                                } else t(this).unbind(C, w);
                                (P = !0), t(this).css(g), h && h.call(this);
                            }),
                            n > 0 &&
                                (this.bind(C, w),
                                setTimeout(function () {
                                    P || w.call(A);
                                }, 1e3 * (n + v) + 25)),
                            this.size() && this.get(0).clientLeft,
                            this.css(_),
                            n <= 0 &&
                                setTimeout(function () {
                                    A.each(function () {
                                        w.call(this);
                                    });
                                }, 0),
                            this
                        );
                    }),
                    (h = null);
            })(n),
            (function (t, e) {
                window.document;
                var n = t.fn.show,
                    o = t.fn.hide,
                    r = t.fn.toggle;
                function i(e, n, o, r, i) {
                    "function" != typeof n || i || ((i = n), (n = void 0));
                    var s = { opacity: o };
                    return r && ((s.scale = r), e.css(t.fx.cssPrefix + "transform-origin", "0 0")), e.animate(s, n, null, i);
                }
                function s(e, n, r, s) {
                    return i(e, n, 0, r, function () {
                        o.call(t(this)), s && s.call(this);
                    });
                }
                (t.fn.show = function (t, e) {
                    return n.call(this), void 0 === t ? (t = 0) : this.css("opacity", 0), i(this, t, 1, "1,1", e);
                }),
                    (t.fn.hide = function (t, e) {
                        return void 0 === t ? o.call(this) : s(this, t, "0,0", e);
                    }),
                    (t.fn.toggle = function (e, n) {
                        return void 0 === e || "boolean" == typeof e
                            ? r.call(this, e)
                            : this.each(function () {
                                  var o = t(this);
                                  o["none" == o.css("display") ? "show" : "hide"](e, n);
                              });
                    }),
                    (t.fn.fadeTo = function (t, e, n) {
                        return i(this, t, e, null, n);
                    }),
                    (t.fn.fadeIn = function (t, e) {
                        var o = this.css("opacity");
                        return o > 0 ? this.css("opacity", 0) : (o = 1), n.call(this).fadeTo(t, o, e);
                    }),
                    (t.fn.fadeOut = function (t, e) {
                        return s(this, t, null, e);
                    }),
                    (t.fn.fadeToggle = function (e, n) {
                        return this.each(function () {
                            var o = t(this);
                            o[0 == o.css("opacity") || "none" == o.css("display") ? "fadeIn" : "fadeOut"](e, n);
                        });
                    });
            })(n),
            (function (t) {
                var e = {},
                    n = t.fn.data,
                    o = t.camelCase,
                    r = (t.expando = "Zepto" + +new Date()),
                    i = [];
                function s(n, s, a) {
                    var c = n[r] || (n[r] = ++t.uuid),
                        u =
                            e[c] ||
                            (e[c] = (function (e) {
                                var n = {};
                                return (
                                    t.each(e.attributes || i, function (e, r) {
                                        0 == r.name.indexOf("data-") && (n[o(r.name.replace("data-", ""))] = t.zepto.deserializeValue(r.value));
                                    }),
                                    n
                                );
                            })(n));
                    return void 0 !== s && (u[o(s)] = a), u;
                }
                (t.fn.data = function (i, a) {
                    return void 0 === a
                        ? t.isPlainObject(i)
                            ? this.each(function (e, n) {
                                  t.each(i, function (t, e) {
                                      s(n, t, e);
                                  });
                              })
                            : 0 in this
                            ? (function (i, a) {
                                  var c = i[r],
                                      u = c && e[c];
                                  if (void 0 === a) return u || s(i);
                                  if (u) {
                                      if (a in u) return u[a];
                                      var l = o(a);
                                      if (l in u) return u[l];
                                  }
                                  return n.call(t(i), a);
                              })(this[0], i)
                            : void 0
                        : this.each(function () {
                              s(this, i, a);
                          });
                }),
                    (t.data = function (e, n, o) {
                        return t(e).data(n, o);
                    }),
                    (t.hasData = function (n) {
                        var o = n[r],
                            i = o && e[o];
                        return !!i && !t.isEmptyObject(i);
                    }),
                    (t.fn.removeData = function (n) {
                        return (
                            "string" == typeof n && (n = n.split(/\s+/)),
                            this.each(function () {
                                var i = this[r],
                                    s = i && e[i];
                                s &&
                                    t.each(n || s, function (t) {
                                        delete s[n ? o(this) : t];
                                    });
                            })
                        );
                    }),
                    ["remove", "empty"].forEach(function (e) {
                        var n = t.fn[e];
                        t.fn[e] = function () {
                            var t = this.find("*");
                            return "remove" === e && (t = t.add(this)), t.removeData(), n.call(this);
                        };
                    });
            })(n);
    },
    function (t, e, n) {
        "use strict";
        (function (t) {
            var e = n(10),
                o = n(6),
                r = (function () {
                    if ("undefined" != typeof self) return self;
                    if ("undefined" != typeof window) return window;
                    if (void 0 !== t) return t;
                    throw new Error("unable to locate global object");
                })();
            "Promise" in r ? r.Promise.prototype.finally || (r.Promise.prototype.finally = o.a) : (r.Promise = e.a);
        }.call(this, n(5)));
    },
    function (t, e, n) {
        (function (t) {
            var o = (void 0 !== t && t) || ("undefined" != typeof self && self) || window,
                r = Function.prototype.apply;
            function i(t, e) {
                (this._id = t), (this._clearFn = e);
            }
            (e.setTimeout = function () {
                return new i(r.call(setTimeout, o, arguments), clearTimeout);
            }),
                (e.setInterval = function () {
                    return new i(r.call(setInterval, o, arguments), clearInterval);
                }),
                (e.clearTimeout = e.clearInterval = function (t) {
                    t && t.close();
                }),
                (i.prototype.unref = i.prototype.ref = function () {}),
                (i.prototype.close = function () {
                    this._clearFn.call(o, this._id);
                }),
                (e.enroll = function (t, e) {
                    clearTimeout(t._idleTimeoutId), (t._idleTimeout = e);
                }),
                (e.unenroll = function (t) {
                    clearTimeout(t._idleTimeoutId), (t._idleTimeout = -1);
                }),
                (e._unrefActive = e.active = function (t) {
                    clearTimeout(t._idleTimeoutId);
                    var e = t._idleTimeout;
                    e >= 0 &&
                        (t._idleTimeoutId = setTimeout(function () {
                            t._onTimeout && t._onTimeout();
                        }, e));
                }),
                n(17),
                (e.setImmediate = ("undefined" != typeof self && self.setImmediate) || (void 0 !== t && t.setImmediate) || (this && this.setImmediate)),
                (e.clearImmediate = ("undefined" != typeof self && self.clearImmediate) || (void 0 !== t && t.clearImmediate) || (this && this.clearImmediate));
        }.call(this, n(5)));
    },
    function (t, e, n) {
        (function (t, e) {
            !(function (t, n) {
                "use strict";
                if (!t.setImmediate) {
                    var o,
                        r,
                        i,
                        s,
                        a,
                        c = 1,
                        u = {},
                        l = !1,
                        d = t.document,
                        p = Object.getPrototypeOf && Object.getPrototypeOf(t);
                    (p = p && p.setTimeout ? p : t),
                        "[object process]" === {}.toString.call(t.process)
                            ? (o = function (t) {
                                  e.nextTick(function () {
                                      f(t);
                                  });
                              })
                            : !(function () {
                                  if (t.postMessage && !t.importScripts) {
                                      var e = !0,
                                          n = t.onmessage;
                                      return (
                                          (t.onmessage = function () {
                                              e = !1;
                                          }),
                                          t.postMessage("", "*"),
                                          (t.onmessage = n),
                                          e
                                      );
                                  }
                              })()
                            ? t.MessageChannel
                                ? (((i = new MessageChannel()).port1.onmessage = function (t) {
                                      f(t.data);
                                  }),
                                  (o = function (t) {
                                      i.port2.postMessage(t);
                                  }))
                                : d && "onreadystatechange" in d.createElement("script")
                                ? ((r = d.documentElement),
                                  (o = function (t) {
                                      var e = d.createElement("script");
                                      (e.onreadystatechange = function () {
                                          f(t), (e.onreadystatechange = null), r.removeChild(e), (e = null);
                                      }),
                                          r.appendChild(e);
                                  }))
                                : (o = function (t) {
                                      setTimeout(f, 0, t);
                                  })
                            : ((s = "setImmediate$" + Math.random() + "$"),
                              (a = function (e) {
                                  e.source === t && "string" == typeof e.data && 0 === e.data.indexOf(s) && f(+e.data.slice(s.length));
                              }),
                              t.addEventListener ? t.addEventListener("message", a, !1) : t.attachEvent("onmessage", a),
                              (o = function (e) {
                                  t.postMessage(s + e, "*");
                              })),
                        (p.setImmediate = function (t) {
                            "function" != typeof t && (t = new Function("" + t));
                            for (var e = new Array(arguments.length - 1), n = 0; n < e.length; n++) e[n] = arguments[n + 1];
                            var r = { callback: t, args: e };
                            return (u[c] = r), o(c), c++;
                        }),
                        (p.clearImmediate = h);
                }
                function h(t) {
                    delete u[t];
                }
                function f(t) {
                    if (l) setTimeout(f, 0, t);
                    else {
                        var e = u[t];
                        if (e) {
                            l = !0;
                            try {
                                !(function (t) {
                                    var e = t.callback,
                                        n = t.args;
                                    switch (n.length) {
                                        case 0:
                                            e();
                                            break;
                                        case 1:
                                            e(n[0]);
                                            break;
                                        case 2:
                                            e(n[0], n[1]);
                                            break;
                                        case 3:
                                            e(n[0], n[1], n[2]);
                                            break;
                                        default:
                                            e.apply(void 0, n);
                                    }
                                })(e);
                            } finally {
                                h(t), (l = !1);
                            }
                        }
                    }
                }
            })("undefined" == typeof self ? (void 0 === t ? this : t) : self);
        }.call(this, n(5), n(18)));
    },
    function (t, e) {
        var n,
            o,
            r = (t.exports = {});
        function i() {
            throw new Error("setTimeout has not been defined");
        }
        function s() {
            throw new Error("clearTimeout has not been defined");
        }
        function a(t) {
            if (n === setTimeout) return setTimeout(t, 0);
            if ((n === i || !n) && setTimeout) return (n = setTimeout), setTimeout(t, 0);
            try {
                return n(t, 0);
            } catch (e) {
                try {
                    return n.call(null, t, 0);
                } catch (e) {
                    return n.call(this, t, 0);
                }
            }
        }
        !(function () {
            try {
                n = "function" == typeof setTimeout ? setTimeout : i;
            } catch (t) {
                n = i;
            }
            try {
                o = "function" == typeof clearTimeout ? clearTimeout : s;
            } catch (t) {
                o = s;
            }
        })();
        var c,
            u = [],
            l = !1,
            d = -1;
        function p() {
            l && c && ((l = !1), c.length ? (u = c.concat(u)) : (d = -1), u.length && h());
        }
        function h() {
            if (!l) {
                var t = a(p);
                l = !0;
                for (var e = u.length; e; ) {
                    for (c = u, u = []; ++d < e; ) c && c[d].run();
                    (d = -1), (e = u.length);
                }
                (c = null),
                    (l = !1),
                    (function (t) {
                        if (o === clearTimeout) return clearTimeout(t);
                        if ((o === s || !o) && clearTimeout) return (o = clearTimeout), clearTimeout(t);
                        try {
                            o(t);
                        } catch (e) {
                            try {
                                return o.call(null, t);
                            } catch (e) {
                                return o.call(this, t);
                            }
                        }
                    })(t);
            }
        }
        function f(t, e) {
            (this.fun = t), (this.array = e);
        }
        function g() {}
        (r.nextTick = function (t) {
            var e = new Array(arguments.length - 1);
            if (arguments.length > 1) for (var n = 1; n < arguments.length; n++) e[n - 1] = arguments[n];
            u.push(new f(t, e)), 1 !== u.length || l || a(h);
        }),
            (f.prototype.run = function () {
                this.fun.apply(null, this.array);
            }),
            (r.title = "browser"),
            (r.browser = !0),
            (r.env = {}),
            (r.argv = []),
            (r.version = ""),
            (r.versions = {}),
            (r.on = g),
            (r.addListener = g),
            (r.once = g),
            (r.off = g),
            (r.removeListener = g),
            (r.removeAllListeners = g),
            (r.emit = g),
            (r.prependListener = g),
            (r.prependOnceListener = g),
            (r.listeners = function (t) {
                return [];
            }),
            (r.binding = function (t) {
                throw new Error("process.binding is not supported");
            }),
            (r.cwd = function () {
                return "/";
            }),
            (r.chdir = function (t) {
                throw new Error("process.chdir is not supported");
            }),
            (r.umask = function () {
                return 0;
            });
    },
    function (t, e, n) {
        "use strict";
        n.r(e),
            n.d(e, "loadWidget", function () {
                return ws;
            }),
            n.d(e, "loadCSS", function () {
                return Ps;
            }),
            n.d(e, "_t", function () {
                return Cs;
            }),
            n.d(e, "z", function () {
                return oo;
            }),
            n.d(e, "Templates", function () {
                return so;
            }),
            n.d(e, "Templating", function () {
                return Do;
            }),
            n.d(e, "EventBus", function () {
                return o;
            }),
            n.d(e, "Events", function () {
                return Fo;
            }),
            n.d(e, "Logger", function () {
                return to;
            }),
            n.d(e, "Globals", function () {
                return r;
            }),
            n.d(e, "Ui", function () {
                return As;
            }),
            n.d(e, "Widgets", function () {
                return ys;
            }),
            n.d(e, "Themes", function () {
                return Wn;
            }),
            n.d(e, "Utils", function () {
                return Es;
            });
        n(14), n(15);
        var o = (function () {
                function t() {
                    (this.listeners = {}), (this.alive = !0), (this.listeners = {});
                }
                return (
                    (t.prototype.on = function (t, e, n) {
                        for (var o = [], r = arguments.length, i = 0; i < r; i += 1) o.push(arguments[i]);
                        (o = o.length > 3 ? o.splice(3, o.length - 1) : []), void 0 !== this.listeners[t] ? this.listeners[t].push({ scope: n, callback: e, args: o }) : (this.listeners[t] = [{ scope: n, callback: e, args: o }]);
                    }),
                    (t.prototype.off = function (t, e, n) {
                        if (void 0 !== this.listeners[t]) {
                            for (var o = this.listeners[t].length, r = [], i = 0; i < o; i += 1) {
                                var s = this.listeners[t][i];
                                (s.scope === n && s.callback === e) || r.push(s);
                            }
                            this.listeners[t] = r;
                        }
                    }),
                    (t.prototype.has = function (t, e, n) {
                        if (void 0 !== this.listeners[t]) {
                            var o = this.listeners[t].length;
                            if (void 0 === e && void 0 === n) return o > 0;
                            for (var r = 0; r < o; r += 1) {
                                var i = this.listeners[t][r];
                                if ((!n || i.scope === n) && i.callback === e) return !0;
                            }
                        }
                        return !1;
                    }),
                    (t.prototype.trigger = function (t) {
                        for (var e = [], n = 1; n < arguments.length; n++) e[n - 1] = arguments[n];
                        var o = 0;
                        if (void 0 !== this.listeners[t])
                            for (var r = this.listeners[t].length, i = 0; i < r; i += 1) {
                                var s = this.listeners[t][i];
                                if (s && s.callback) {
                                    var a = e.concat(s.args);
                                    s.callback.apply(s.scope, a), (o += 1);
                                }
                            }
                        return o;
                    }),
                    (t.prototype.getEvents = function () {
                        var t = "";
                        for (var e in this.listeners)
                            if (Object.prototype.hasOwnProperty.call(this.listeners, e))
                                for (var n = this.listeners[e].length, o = 0; o < n; o += 1) {
                                    var r = this.listeners[e][o];
                                    (t += r.scope && r.scope.className ? r.scope.className : "anonymous"), (t += " listen for '" + e + "'\n");
                                }
                        return t;
                    }),
                    (t.prototype.destroy = function () {
                        (this.listeners = {}), (this.alive = !1);
                    }),
                    t
                );
            })(),
            r = { POST_CLICK_ACTION_OPEN_POPUP: "open-popup", POST_CLICK_ACTION_GOTO_SOURCE: "goto-source", POST_CLICK_ACTION_NOTHING: "nothing", NETWORK_RSS: 10 };
        var i = function () {
            (this.__data__ = []), (this.size = 0);
        };
        var s = function (t, e) {
            return t === e || (t != t && e != e);
        };
        var a = function (t, e) {
                for (var n = t.length; n--; ) if (s(t[n][0], e)) return n;
                return -1;
            },
            c = Array.prototype.splice;
        var u = function (t) {
            var e = this.__data__,
                n = a(e, t);
            return !(n < 0) && (n == e.length - 1 ? e.pop() : c.call(e, n, 1), --this.size, !0);
        };
        var l = function (t) {
            var e = this.__data__,
                n = a(e, t);
            return n < 0 ? void 0 : e[n][1];
        };
        var d = function (t) {
            return a(this.__data__, t) > -1;
        };
        var p = function (t, e) {
            var n = this.__data__,
                o = a(n, t);
            return o < 0 ? (++this.size, n.push([t, e])) : (n[o][1] = e), this;
        };
        function h(t) {
            var e = -1,
                n = null == t ? 0 : t.length;
            for (this.clear(); ++e < n; ) {
                var o = t[e];
                this.set(o[0], o[1]);
            }
        }
        (h.prototype.clear = i), (h.prototype.delete = u), (h.prototype.get = l), (h.prototype.has = d), (h.prototype.set = p);
        var f = h;
        var g = function () {
            (this.__data__ = new f()), (this.size = 0);
        };
        var v = function (t) {
            var e = this.__data__,
                n = e.delete(t);
            return (this.size = e.size), n;
        };
        var m = function (t) {
            return this.__data__.get(t);
        };
        var y = function (t) {
                return this.__data__.has(t);
            },
            w = n(0),
            _ = w.a.Symbol,
            b = Object.prototype,
            A = b.hasOwnProperty,
            C = b.toString,
            P = _ ? _.toStringTag : void 0;
        var E = function (t) {
                var e = A.call(t, P),
                    n = t[P];
                try {
                    t[P] = void 0;
                    var o = !0;
                } catch (t) {}
                var r = C.call(t);
                return o && (e ? (t[P] = n) : delete t[P]), r;
            },
            k = Object.prototype.toString;
        var O = function (t) {
                return k.call(t);
            },
            T = _ ? _.toStringTag : void 0;
        var S = function (t) {
            return null == t ? (void 0 === t ? "[object Undefined]" : "[object Null]") : T && T in Object(t) ? E(t) : O(t);
        };
        var D = function (t) {
            var e = typeof t;
            return null != t && ("object" == e || "function" == e);
        };
        var F,
            L = function (t) {
                if (!D(t)) return !1;
                var e = S(t);
                return "[object Function]" == e || "[object GeneratorFunction]" == e || "[object AsyncFunction]" == e || "[object Proxy]" == e;
            },
            x = w.a["__core-js_shared__"],
            I = (F = /[^.]+$/.exec((x && x.keys && x.keys.IE_PROTO) || "")) ? "Symbol(src)_1." + F : "";
        var j = function (t) {
                return !!I && I in t;
            },
            B = Function.prototype.toString;
        var $ = function (t) {
                if (null != t) {
                    try {
                        return B.call(t);
                    } catch (t) {}
                    try {
                        return t + "";
                    } catch (t) {}
                }
                return "";
            },
            N = /^\[object .+?Constructor\]$/,
            H = Function.prototype,
            M = Object.prototype,
            R = H.toString,
            W = M.hasOwnProperty,
            G = RegExp(
                "^" +
                    R.call(W)
                        .replace(/[\\^$.*+?()[\]{}|]/g, "\\$&")
                        .replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") +
                    "$"
            );
        var z = function (t) {
            return !(!D(t) || j(t)) && (L(t) ? G : N).test($(t));
        };
        var V = function (t, e) {
            return null == t ? void 0 : t[e];
        };
        var U = function (t, e) {
                var n = V(t, e);
                return z(n) ? n : void 0;
            },
            Z = U(w.a, "Map"),
            X = U(Object, "create");
        var q = function () {
            (this.__data__ = X ? X(null) : {}), (this.size = 0);
        };
        var K = function (t) {
                var e = this.has(t) && delete this.__data__[t];
                return (this.size -= e ? 1 : 0), e;
            },
            J = Object.prototype.hasOwnProperty;
        var Y = function (t) {
                var e = this.__data__;
                if (X) {
                    var n = e[t];
                    return "__lodash_hash_undefined__" === n ? void 0 : n;
                }
                return J.call(e, t) ? e[t] : void 0;
            },
            Q = Object.prototype.hasOwnProperty;
        var tt = function (t) {
            var e = this.__data__;
            return X ? void 0 !== e[t] : Q.call(e, t);
        };
        var et = function (t, e) {
            var n = this.__data__;
            return (this.size += this.has(t) ? 0 : 1), (n[t] = X && void 0 === e ? "__lodash_hash_undefined__" : e), this;
        };
        function nt(t) {
            var e = -1,
                n = null == t ? 0 : t.length;
            for (this.clear(); ++e < n; ) {
                var o = t[e];
                this.set(o[0], o[1]);
            }
        }
        (nt.prototype.clear = q), (nt.prototype.delete = K), (nt.prototype.get = Y), (nt.prototype.has = tt), (nt.prototype.set = et);
        var ot = nt;
        var rt = function () {
            (this.size = 0), (this.__data__ = { hash: new ot(), map: new (Z || f)(), string: new ot() });
        };
        var it = function (t) {
            var e = typeof t;
            return "string" == e || "number" == e || "symbol" == e || "boolean" == e ? "__proto__" !== t : null === t;
        };
        var st = function (t, e) {
            var n = t.__data__;
            return it(e) ? n["string" == typeof e ? "string" : "hash"] : n.map;
        };
        var at = function (t) {
            var e = st(this, t).delete(t);
            return (this.size -= e ? 1 : 0), e;
        };
        var ct = function (t) {
            return st(this, t).get(t);
        };
        var ut = function (t) {
            return st(this, t).has(t);
        };
        var lt = function (t, e) {
            var n = st(this, t),
                o = n.size;
            return n.set(t, e), (this.size += n.size == o ? 0 : 1), this;
        };
        function dt(t) {
            var e = -1,
                n = null == t ? 0 : t.length;
            for (this.clear(); ++e < n; ) {
                var o = t[e];
                this.set(o[0], o[1]);
            }
        }
        (dt.prototype.clear = rt), (dt.prototype.delete = at), (dt.prototype.get = ct), (dt.prototype.has = ut), (dt.prototype.set = lt);
        var pt = dt;
        var ht = function (t, e) {
            var n = this.__data__;
            if (n instanceof f) {
                var o = n.__data__;
                if (!Z || o.length < 199) return o.push([t, e]), (this.size = ++n.size), this;
                n = this.__data__ = new pt(o);
            }
            return n.set(t, e), (this.size = n.size), this;
        };
        function ft(t) {
            var e = (this.__data__ = new f(t));
            this.size = e.size;
        }
        (ft.prototype.clear = g), (ft.prototype.delete = v), (ft.prototype.get = m), (ft.prototype.has = y), (ft.prototype.set = ht);
        var gt = ft;
        var vt = function (t, e) {
                for (var n = -1, o = null == t ? 0 : t.length; ++n < o && !1 !== e(t[n], n, t); );
                return t;
            },
            mt = (function () {
                try {
                    var t = U(Object, "defineProperty");
                    return t({}, "", {}), t;
                } catch (t) {}
            })();
        var yt = function (t, e, n) {
                "__proto__" == e && mt ? mt(t, e, { configurable: !0, enumerable: !0, value: n, writable: !0 }) : (t[e] = n);
            },
            wt = Object.prototype.hasOwnProperty;
        var _t = function (t, e, n) {
            var o = t[e];
            (wt.call(t, e) && s(o, n) && (void 0 !== n || e in t)) || yt(t, e, n);
        };
        var bt = function (t, e, n, o) {
            var r = !n;
            n || (n = {});
            for (var i = -1, s = e.length; ++i < s; ) {
                var a = e[i],
                    c = o ? o(n[a], t[a], a, n, t) : void 0;
                void 0 === c && (c = t[a]), r ? yt(n, a, c) : _t(n, a, c);
            }
            return n;
        };
        var At = function (t, e) {
            for (var n = -1, o = Array(t); ++n < t; ) o[n] = e(n);
            return o;
        };
        var Ct = function (t) {
            return null != t && "object" == typeof t;
        };
        var Pt = function (t) {
                return Ct(t) && "[object Arguments]" == S(t);
            },
            Et = Object.prototype,
            kt = Et.hasOwnProperty,
            Ot = Et.propertyIsEnumerable,
            Tt = Pt(
                (function () {
                    return arguments;
                })()
            )
                ? Pt
                : function (t) {
                      return Ct(t) && kt.call(t, "callee") && !Ot.call(t, "callee");
                  },
            St = Array.isArray,
            Dt = n(3),
            Ft = /^(?:0|[1-9]\d*)$/;
        var Lt = function (t, e) {
            var n = typeof t;
            return !!(e = null == e ? 9007199254740991 : e) && ("number" == n || ("symbol" != n && Ft.test(t))) && t > -1 && t % 1 == 0 && t < e;
        };
        var xt = function (t) {
                return "number" == typeof t && t > -1 && t % 1 == 0 && t <= 9007199254740991;
            },
            It = {};
        (It["[object Float32Array]"] = It["[object Float64Array]"] = It["[object Int8Array]"] = It["[object Int16Array]"] = It["[object Int32Array]"] = It["[object Uint8Array]"] = It["[object Uint8ClampedArray]"] = It[
            "[object Uint16Array]"
        ] = It["[object Uint32Array]"] = !0),
            (It["[object Arguments]"] = It["[object Array]"] = It["[object ArrayBuffer]"] = It["[object Boolean]"] = It["[object DataView]"] = It["[object Date]"] = It["[object Error]"] = It["[object Function]"] = It["[object Map]"] = It[
                "[object Number]"
            ] = It["[object Object]"] = It["[object RegExp]"] = It["[object Set]"] = It["[object String]"] = It["[object WeakMap]"] = !1);
        var jt = function (t) {
            return Ct(t) && xt(t.length) && !!It[S(t)];
        };
        var Bt = function (t) {
                return function (e) {
                    return t(e);
                };
            },
            $t = n(4),
            Nt = $t.a && $t.a.isTypedArray,
            Ht = Nt ? Bt(Nt) : jt,
            Mt = Object.prototype.hasOwnProperty;
        var Rt = function (t, e) {
                var n = St(t),
                    o = !n && Tt(t),
                    r = !n && !o && Object(Dt.a)(t),
                    i = !n && !o && !r && Ht(t),
                    s = n || o || r || i,
                    a = s ? At(t.length, String) : [],
                    c = a.length;
                for (var u in t) (!e && !Mt.call(t, u)) || (s && ("length" == u || (r && ("offset" == u || "parent" == u)) || (i && ("buffer" == u || "byteLength" == u || "byteOffset" == u)) || Lt(u, c))) || a.push(u);
                return a;
            },
            Wt = Object.prototype;
        var Gt = function (t) {
            var e = t && t.constructor;
            return t === (("function" == typeof e && e.prototype) || Wt);
        };
        var zt = function (t, e) {
                return function (n) {
                    return t(e(n));
                };
            },
            Vt = zt(Object.keys, Object),
            Ut = Object.prototype.hasOwnProperty;
        var Zt = function (t) {
            if (!Gt(t)) return Vt(t);
            var e = [];
            for (var n in Object(t)) Ut.call(t, n) && "constructor" != n && e.push(n);
            return e;
        };
        var Xt = function (t) {
            return null != t && xt(t.length) && !L(t);
        };
        var qt = function (t) {
            return Xt(t) ? Rt(t) : Zt(t);
        };
        var Kt = function (t, e) {
            return t && bt(e, qt(e), t);
        };
        var Jt = function (t) {
                var e = [];
                if (null != t) for (var n in Object(t)) e.push(n);
                return e;
            },
            Yt = Object.prototype.hasOwnProperty;
        var Qt = function (t) {
            if (!D(t)) return Jt(t);
            var e = Gt(t),
                n = [];
            for (var o in t) ("constructor" != o || (!e && Yt.call(t, o))) && n.push(o);
            return n;
        };
        var te = function (t) {
            return Xt(t) ? Rt(t, !0) : Qt(t);
        };
        var ee = function (t, e) {
                return t && bt(e, te(e), t);
            },
            ne = n(8);
        var oe = function (t, e) {
            var n = -1,
                o = t.length;
            for (e || (e = Array(o)); ++n < o; ) e[n] = t[n];
            return e;
        };
        var re = function (t, e) {
            for (var n = -1, o = null == t ? 0 : t.length, r = 0, i = []; ++n < o; ) {
                var s = t[n];
                e(s, n, t) && (i[r++] = s);
            }
            return i;
        };
        var ie = function () {
                return [];
            },
            se = Object.prototype.propertyIsEnumerable,
            ae = Object.getOwnPropertySymbols,
            ce = ae
                ? function (t) {
                      return null == t
                          ? []
                          : ((t = Object(t)),
                            re(ae(t), function (e) {
                                return se.call(t, e);
                            }));
                  }
                : ie;
        var ue = function (t, e) {
            return bt(t, ce(t), e);
        };
        var le = function (t, e) {
                for (var n = -1, o = e.length, r = t.length; ++n < o; ) t[r + n] = e[n];
                return t;
            },
            de = zt(Object.getPrototypeOf, Object),
            pe = Object.getOwnPropertySymbols
                ? function (t) {
                      for (var e = []; t; ) le(e, ce(t)), (t = de(t));
                      return e;
                  }
                : ie;
        var he = function (t, e) {
            return bt(t, pe(t), e);
        };
        var fe = function (t, e, n) {
            var o = e(t);
            return St(t) ? o : le(o, n(t));
        };
        var ge = function (t) {
            return fe(t, qt, ce);
        };
        var ve = function (t) {
                return fe(t, te, pe);
            },
            me = U(w.a, "DataView"),
            ye = U(w.a, "Promise"),
            we = U(w.a, "Set"),
            _e = U(w.a, "WeakMap"),
            be = $(me),
            Ae = $(Z),
            Ce = $(ye),
            Pe = $(we),
            Ee = $(_e),
            ke = S;
        ((me && "[object DataView]" != ke(new me(new ArrayBuffer(1)))) ||
            (Z && "[object Map]" != ke(new Z())) ||
            (ye && "[object Promise]" != ke(ye.resolve())) ||
            (we && "[object Set]" != ke(new we())) ||
            (_e && "[object WeakMap]" != ke(new _e()))) &&
            (ke = function (t) {
                var e = S(t),
                    n = "[object Object]" == e ? t.constructor : void 0,
                    o = n ? $(n) : "";
                if (o)
                    switch (o) {
                        case be:
                            return "[object DataView]";
                        case Ae:
                            return "[object Map]";
                        case Ce:
                            return "[object Promise]";
                        case Pe:
                            return "[object Set]";
                        case Ee:
                            return "[object WeakMap]";
                    }
                return e;
            });
        var Oe = ke,
            Te = Object.prototype.hasOwnProperty;
        var Se = function (t) {
                var e = t.length,
                    n = new t.constructor(e);
                return e && "string" == typeof t[0] && Te.call(t, "index") && ((n.index = t.index), (n.input = t.input)), n;
            },
            De = w.a.Uint8Array;
        var Fe = function (t) {
            var e = new t.constructor(t.byteLength);
            return new De(e).set(new De(t)), e;
        };
        var Le = function (t, e) {
                var n = e ? Fe(t.buffer) : t.buffer;
                return new t.constructor(n, t.byteOffset, t.byteLength);
            },
            xe = /\w*$/;
        var Ie = function (t) {
                var e = new t.constructor(t.source, xe.exec(t));
                return (e.lastIndex = t.lastIndex), e;
            },
            je = _ ? _.prototype : void 0,
            Be = je ? je.valueOf : void 0;
        var $e = function (t) {
            return Be ? Object(Be.call(t)) : {};
        };
        var Ne = function (t, e) {
            var n = e ? Fe(t.buffer) : t.buffer;
            return new t.constructor(n, t.byteOffset, t.length);
        };
        var He = function (t, e, n) {
                var o = t.constructor;
                switch (e) {
                    case "[object ArrayBuffer]":
                        return Fe(t);
                    case "[object Boolean]":
                    case "[object Date]":
                        return new o(+t);
                    case "[object DataView]":
                        return Le(t, n);
                    case "[object Float32Array]":
                    case "[object Float64Array]":
                    case "[object Int8Array]":
                    case "[object Int16Array]":
                    case "[object Int32Array]":
                    case "[object Uint8Array]":
                    case "[object Uint8ClampedArray]":
                    case "[object Uint16Array]":
                    case "[object Uint32Array]":
                        return Ne(t, n);
                    case "[object Map]":
                        return new o();
                    case "[object Number]":
                    case "[object String]":
                        return new o(t);
                    case "[object RegExp]":
                        return Ie(t);
                    case "[object Set]":
                        return new o();
                    case "[object Symbol]":
                        return $e(t);
                }
            },
            Me = Object.create,
            Re = (function () {
                function t() {}
                return function (e) {
                    if (!D(e)) return {};
                    if (Me) return Me(e);
                    t.prototype = e;
                    var n = new t();
                    return (t.prototype = void 0), n;
                };
            })();
        var We = function (t) {
            return "function" != typeof t.constructor || Gt(t) ? {} : Re(de(t));
        };
        var Ge = function (t) {
                return Ct(t) && "[object Map]" == Oe(t);
            },
            ze = $t.a && $t.a.isMap,
            Ve = ze ? Bt(ze) : Ge;
        var Ue = function (t) {
                return Ct(t) && "[object Set]" == Oe(t);
            },
            Ze = $t.a && $t.a.isSet,
            Xe = Ze ? Bt(Ze) : Ue,
            qe = {};
        (qe["[object Arguments]"] = qe["[object Array]"] = qe["[object ArrayBuffer]"] = qe["[object DataView]"] = qe["[object Boolean]"] = qe["[object Date]"] = qe["[object Float32Array]"] = qe["[object Float64Array]"] = qe[
            "[object Int8Array]"
        ] = qe["[object Int16Array]"] = qe["[object Int32Array]"] = qe["[object Map]"] = qe["[object Number]"] = qe["[object Object]"] = qe["[object RegExp]"] = qe["[object Set]"] = qe["[object String]"] = qe["[object Symbol]"] = qe[
            "[object Uint8Array]"
        ] = qe["[object Uint8ClampedArray]"] = qe["[object Uint16Array]"] = qe["[object Uint32Array]"] = !0),
            (qe["[object Error]"] = qe["[object Function]"] = qe["[object WeakMap]"] = !1);
        var Ke = function t(e, n, o, r, i, s) {
            var a,
                c = 1 & n,
                u = 2 & n,
                l = 4 & n;
            if ((o && (a = i ? o(e, r, i, s) : o(e)), void 0 !== a)) return a;
            if (!D(e)) return e;
            var d = St(e);
            if (d) {
                if (((a = Se(e)), !c)) return oe(e, a);
            } else {
                var p = Oe(e),
                    h = "[object Function]" == p || "[object GeneratorFunction]" == p;
                if (Object(Dt.a)(e)) return Object(ne.a)(e, c);
                if ("[object Object]" == p || "[object Arguments]" == p || (h && !i)) {
                    if (((a = u || h ? {} : We(e)), !c)) return u ? he(e, ee(a, e)) : ue(e, Kt(a, e));
                } else {
                    if (!qe[p]) return i ? e : {};
                    a = He(e, p, c);
                }
            }
            s || (s = new gt());
            var f = s.get(e);
            if (f) return f;
            s.set(e, a),
                Xe(e)
                    ? e.forEach(function (r) {
                          a.add(t(r, n, o, r, e, s));
                      })
                    : Ve(e) &&
                      e.forEach(function (r, i) {
                          a.set(i, t(r, n, o, i, e, s));
                      });
            var g = l ? (u ? ve : ge) : u ? keysIn : qt,
                v = d ? void 0 : g(e);
            return (
                vt(v || e, function (r, i) {
                    v && (r = e[(i = r)]), _t(a, i, t(r, n, o, i, e, s));
                }),
                a
            );
        };
        var Je = function (t) {
            return Ke(t, 5);
        };
        var Ye = function (t, e, n) {
            switch (n.length) {
                case 0:
                    return t.call(e);
                case 1:
                    return t.call(e, n[0]);
                case 2:
                    return t.call(e, n[0], n[1]);
                case 3:
                    return t.call(e, n[0], n[1], n[2]);
            }
            return t.apply(e, n);
        };
        var Qe = function (t) {
                return t;
            },
            tn = Math.max;
        var en = function (t, e, n) {
            return (
                (e = tn(void 0 === e ? t.length - 1 : e, 0)),
                function () {
                    for (var o = arguments, r = -1, i = tn(o.length - e, 0), s = Array(i); ++r < i; ) s[r] = o[e + r];
                    r = -1;
                    for (var a = Array(e + 1); ++r < e; ) a[r] = o[r];
                    return (a[e] = n(s)), Ye(t, this, a);
                }
            );
        };
        var nn = function (t) {
                return function () {
                    return t;
                };
            },
            on = mt
                ? function (t, e) {
                      return mt(t, "toString", { configurable: !0, enumerable: !1, value: nn(e), writable: !0 });
                  }
                : Qe,
            rn = Date.now;
        var sn = (function (t) {
            var e = 0,
                n = 0;
            return function () {
                var o = rn(),
                    r = 16 - (o - n);
                if (((n = o), r > 0)) {
                    if (++e >= 800) return arguments[0];
                } else e = 0;
                return t.apply(void 0, arguments);
            };
        })(on);
        var an = function (t, e) {
            return sn(en(t, e, Qe), t + "");
        };
        var cn = function (t, e, n) {
            ((void 0 !== n && !s(t[e], n)) || (void 0 === n && !(e in t))) && yt(t, e, n);
        };
        var un = (function (t) {
            return function (e, n, o) {
                for (var r = -1, i = Object(e), s = o(e), a = s.length; a--; ) {
                    var c = s[t ? a : ++r];
                    if (!1 === n(i[c], c, i)) break;
                }
                return e;
            };
        })();
        var ln = function (t) {
                return Ct(t) && Xt(t);
            },
            dn = Function.prototype,
            pn = Object.prototype,
            hn = dn.toString,
            fn = pn.hasOwnProperty,
            gn = hn.call(Object);
        var vn = function (t) {
            if (!Ct(t) || "[object Object]" != S(t)) return !1;
            var e = de(t);
            if (null === e) return !0;
            var n = fn.call(e, "constructor") && e.constructor;
            return "function" == typeof n && n instanceof n && hn.call(n) == gn;
        };
        var mn = function (t, e) {
            if (("constructor" !== e || "function" != typeof t[e]) && "__proto__" != e) return t[e];
        };
        var yn = function (t) {
            return bt(t, te(t));
        };
        var wn = function (t, e, n, o, r, i, s) {
            var a = mn(t, n),
                c = mn(e, n),
                u = s.get(c);
            if (u) cn(t, n, u);
            else {
                var l = i ? i(a, c, n + "", t, e, s) : void 0,
                    d = void 0 === l;
                if (d) {
                    var p = St(c),
                        h = !p && Object(Dt.a)(c),
                        f = !p && !h && Ht(c);
                    (l = c),
                        p || h || f
                            ? St(a)
                                ? (l = a)
                                : ln(a)
                                ? (l = oe(a))
                                : h
                                ? ((d = !1), (l = Object(ne.a)(c, !0)))
                                : f
                                ? ((d = !1), (l = Ne(c, !0)))
                                : (l = [])
                            : vn(c) || Tt(c)
                            ? ((l = a), Tt(a) ? (l = yn(a)) : (D(a) && !L(a)) || (l = We(c)))
                            : (d = !1);
                }
                d && (s.set(c, l), r(l, c, o, i, s), s.delete(c)), cn(t, n, l);
            }
        };
        var _n = function t(e, n, o, r, i) {
            e !== n &&
                un(
                    n,
                    function (s, a) {
                        if ((i || (i = new gt()), D(s))) wn(e, n, a, o, t, r, i);
                        else {
                            var c = r ? r(mn(e, a), s, a + "", e, n, i) : void 0;
                            void 0 === c && (c = s), cn(e, a, c);
                        }
                    },
                    te
                );
        };
        var bn = function t(e, n, o, r, i, s) {
            return D(e) && D(n) && (s.set(n, e), _n(e, n, void 0, t, s), s.delete(n)), e;
        };
        var An = function (t, e, n) {
            if (!D(n)) return !1;
            var o = typeof e;
            return !!("number" == o ? Xt(n) && Lt(e, n.length) : "string" == o && e in n) && s(n[e], t);
        };
        var Cn,
            Pn,
            En = (function (t) {
                return an(function (e, n) {
                    var o = -1,
                        r = n.length,
                        i = r > 1 ? n[r - 1] : void 0,
                        s = r > 2 ? n[2] : void 0;
                    for (i = t.length > 3 && "function" == typeof i ? (r--, i) : void 0, s && An(n[0], n[1], s) && ((i = r < 3 ? void 0 : i), (r = 1)), e = Object(e); ++o < r; ) {
                        var a = n[o];
                        a && t(e, a, o, i);
                    }
                    return e;
                });
            })(function (t, e, n, o) {
                _n(t, e, n, o);
            }),
            kn = an(function (t) {
                return t.push(void 0, bn), Ye(En, void 0, t);
            }),
            On = {
                lang: "en",
                container: "#crt-container",
                debug: !1,
                hidePoweredBy: 1,
                forceHttps: !1,
                feed: { id: "", apiEndpoint: "https://api.curator.io", postsPerPage: 12, params: {}, limit: 25, showAds: !0 },
                widget: { autoLoadNew: !1 },
                post: { template: "post-general", showTitles: !0, showShare: !0, showComments: !1, showLikes: !1, autoPlayVideos: !1, clickAction: "open-popup", clickReadMoreAction: "open-popup" },
                popup: { template: "popup", templateWrapper: "popup-wrapper", autoPlayVideos: !1, deepLink: !1 },
                filter: { template: "filter", showNetworks: !1, showSources: !1, showAll: !1, default: "all", limitPosts: !1, limitPostNumber: 0, period: "" },
            },
            Tn = kn(
                {},
                {
                    post: { template: "post-general", animate: !0, maxHeight: 0 },
                    widget: { template: "widget-waterfall", colWidth: 250, colGutter: 0, showLoadMore: !0, continuousScroll: !1, postsPerPage: 12, animate: !1, progressiveLoad: !1, lazyLoad: !1 },
                },
                On
            ),
            Sn = kn({}, On, {
                post: { template: "post-general", matchHeights: !1, showComments: !1, showLikes: !1, maxHeight: 0, minWidth: 250 },
                widget: {
                    template: "widget-carousel",
                    autoPlay: !0,
                    autoLoad: !0,
                    infinite: !1,
                    controlsOver: !1,
                    controlsShowOnHover: !1,
                    speed: 5e3,
                    duration: 700,
                    panesVisible: -1,
                    useCss: !0,
                    moveAmount: 0,
                    easing: "ease-in-out",
                    progressiveLoad: !1,
                    lazyLoad: !1,
                },
            }),
            Dn = kn(
                {},
                {
                    post: { template: "post-grid", matchHeights: !1, minWidth: 200, imageHeight: "100%" },
                    widget: {
                        template: "widget-grid-carousel",
                        autoPlay: !0,
                        autoLoad: !0,
                        infinite: !1,
                        rows: 2,
                        controlsOver: !0,
                        controlsShowOnHover: !1,
                        speed: 5e3,
                        duration: 700,
                        panesVisible: -1,
                        useCss: !0,
                        moveAmount: 0,
                        easing: null,
                    },
                },
                On
            ),
            Fn = kn(
                {},
                {
                    post: { template: "post-grid", imageHeight: "100%", minWidth: 250 },
                    widget: { animate: !1, continuousScroll: !1, continuousScrollOffset: 50, rows: 3, template: "widget-grid", showLoadMore: !1, loadMoreRows: 1 },
                    responsive: { 480: { widget: { loadMoreRows: 4 } }, 768: { widget: { loadMoreRows: 2 } } },
                },
                On
            ),
            Ln = kn(
                {},
                {
                    post: { template: "post-general", matchHeights: !1, showComments: !1, showLikes: !1, maxHeight: 0, minWidth: 2e3 },
                    widget: { template: "widget-carousel", autoPlay: !0, autoLoad: !0, infinite: !1, controlsOver: !0, controlsShowOnHover: !0, speed: 5e3, duration: 700, panesVisible: -1, useCss: !0, moveAmount: 0, easing: null },
                },
                On
            ),
            xn = kn({}, { post: { template: "post-list", imageWidth: "25%", showComments: !1, showLikes: !1 }, widget: { template: "widget-list", animate: !1, showLoadMore: !0, continuousScroll: !1, postsPerPage: 12 } }, On),
            In = { widgetBgColor: "transparent", bgColor: "#ffffff", borderColor: "#cccccc", iconColor: "#222222", textColor: "#222222", linkColor: "#999999", dateColor: "#000000" },
            jn = function (t) {
                var e = t.bgColor,
                    n = t.textColor,
                    o = t.iconColor,
                    r = t.linkColor,
                    i = t.borderColor;
                return {
                    config: { post: { template: "post-general" } },
                    styles: {
                        widget: { backgroundColor: t.widgetBgColor },
                        post: { backgroundColor: e, borderColor: i, borderWidth: "1px", color: n },
                        postText: { color: n },
                        postTextLink: { color: r },
                        postName: { color: n, textDecoration: "none" },
                        postUsername: { color: n },
                        postIcon: { color: o },
                        postComments: { color: n },
                        postShareIcons: { color: n },
                        postDate: { color: n },
                        loadMore: { color: n, backgroundColor: e, borderColor: i },
                    },
                };
            },
            Bn = function (t) {
                var e = t.bgColor,
                    n = t.textColor,
                    o = t.iconColor,
                    r = t.linkColor,
                    i = t.borderColor;
                return {
                    config: { post: { template: "post-general-london" } },
                    styles: {
                        widget: { backgroundColor: t.widgetBgColor },
                        post: { backgroundColor: e, borderColor: i, borderWidth: "10px", color: n },
                        postText: { color: n },
                        postTextLink: { color: r },
                        postName: { color: n, textDecoration: "none" },
                        postUsername: { color: n },
                        postIcon: { color: o },
                        postComments: { color: n },
                        postShareIcons: { color: n },
                        postDate: { color: n },
                        loadMore: { color: n, backgroundColor: e, borderColor: i },
                        postMaxHeightReadMore: { background: "linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, " + e + " 50%)" },
                    },
                };
            },
            $n = function (t) {
                var e = t.bgColor,
                    n = t.textColor,
                    o = t.iconColor,
                    r = t.linkColor,
                    i = t.borderColor;
                return {
                    config: { post: { template: "post-general-berlin" } },
                    styles: {
                        widget: { backgroundColor: t.widgetBgColor },
                        post: { backgroundColor: "transparent", borderColor: "transparent", borderWidth: "0px", color: n },
                        postText: { color: n },
                        postTextLink: { color: r },
                        postName: { color: n, textDecoration: "none" },
                        postUsername: { color: n },
                        postIcon: { color: o },
                        postComments: { color: n },
                        postShareIcons: { color: n },
                        postDate: { color: n },
                        loadMore: { color: n, backgroundColor: e, borderColor: i },
                    },
                };
            },
            Nn = function (t) {
                var e = t.bgColor,
                    n = t.textColor,
                    o = t.linkColor,
                    r = t.borderColor;
                return {
                    config: { post: { template: "post-grid" } },
                    styles: {
                        gridPost: {},
                        gridPostContent: { backgroundColor: e, color: n },
                        gridPostText: { color: n },
                        gridPostTextLink: { color: o },
                        gridPostHover: { backgroundColor: e, color: n },
                        gridPostHoverLink: { color: o },
                        gridPostIcon: { color: t.iconColor },
                        gridPostFooter: { backgroundColor: e, borderTopColor: r },
                        loadMore: { color: n, backgroundColor: e, borderColor: r },
                        gridPostDate: { color: t.dateColor },
                    },
                };
            },
            Hn = function (t) {
                var e = t.bgColor,
                    n = t.textColor,
                    o = t.linkColor,
                    r = t.borderColor;
                return {
                    config: { post: { template: "post-grid-new-york" } },
                    styles: {
                        gridPost: { padding: "10px", borderRadius: "10px" },
                        gridPostContent: { backgroundColor: e, color: n },
                        gridPostText: { color: n },
                        gridPostTextLink: { color: o },
                        gridPostHover: { backgroundColor: e, color: n },
                        gridPostHoverLink: { color: o },
                        gridPostIcon: { color: t.iconColor },
                        gridPostFooter: { backgroundColor: e },
                        loadMore: { color: n, backgroundColor: e, borderColor: r },
                        gridPostDate: { color: t.dateColor },
                        cssRules: { ".crt-grid-post-new-york .crt-post-footer .crt-post-fullname a,.crt-grid-post-new-york .crt-post-footer .crt-post-username a": { color: n } },
                    },
                };
            },
            Mn = function (t) {
                var e = t.bgColor,
                    n = t.textColor,
                    o = t.linkColor,
                    r = t.borderColor;
                return {
                    config: { post: { template: "post-grid-tokyo" } },
                    styles: {
                        gridPost: { padding: "10px", borderRadius: "10px" },
                        gridPostContent: { backgroundColor: e, color: n },
                        gridPostText: { color: n },
                        gridPostTextLink: { color: o },
                        gridPostHover: { backgroundColor: e, color: n },
                        gridPostHoverLink: { color: o },
                        gridPostIcon: { color: t.iconColor },
                        gridPostFooter: { backgroundColor: e },
                        loadMore: { color: n, backgroundColor: e, borderColor: r },
                        gridPostDate: { color: t.dateColor },
                    },
                };
            },
            Rn = {
                defaultColors: function (t) {
                    var e = In;
                    return "berlin" === t && (e.bgColor = "transparent"), "london" === t && (e.borderColor = e.bgColor), Je(e);
                },
                widgetThemes: function (t) {
                    if (Object.prototype.hasOwnProperty.call(Rn.widgetThemeOptions, t)) {
                        var e = Rn.getThemeType(t);
                        return Object.keys(Rn.widgetThemeOptions[e]);
                    }
                    return console.error("Unknown Widget Type: " + t), [];
                },
                getThemeType: function (t) {
                    switch (t) {
                        case "Carousel":
                            return Cn.CAROUSEL;
                        case "Panel":
                            return Cn.PANEL;
                        case "Grid":
                            return Cn.GRID;
                        case "GridCarousel":
                            return Cn.GRID_CAROUSEL;
                        case "List":
                            return Cn.LIST;
                        default:
                            return Cn.WATER_FALL;
                    }
                },
                defaultTheme: function (t) {
                    var e = Rn.widgetThemes(t);
                    return e ? e[0] : (console.error("Unknown Widget Type: " + t), "sydney");
                },
                typeConfig: function (t) {
                    return Rn.widgetConfigs[t] ? Je(Rn.widgetConfigs[t]) : {};
                },
                themeConfig: function (t, e) {
                    if (Rn.widgetThemeOptions[t] && Rn.widgetThemeOptions[t][e]) {
                        var n = Rn.widgetThemeOptions[t][e]({});
                        return Je(n.config);
                    }
                    return {};
                },
                themeStyles: function (t, e, n) {
                    if (Rn.widgetThemeOptions[t] && Rn.widgetThemeOptions[t][e]) {
                        var o = Rn.widgetThemeOptions[t][e](n);
                        return Je(o.styles);
                    }
                    return {};
                },
                widgetConfigs: { Waterfall: Tn, Carousel: Sn, Panel: Ln, Grid: Fn, GridCarousel: Dn, List: xn },
                widgetThemeOptions: {
                    Waterfall: { sydney: jn, berlin: $n, london: Bn },
                    Carousel: { sydney: jn, berlin: $n, london: Bn },
                    Panel: { sydney: jn, berlin: $n, london: Bn },
                    Grid: { sydney: Nn, "new-york": Hn, tokyo: Mn },
                    GridCarousel: { sydney: Nn, "new-york": Hn, tokyo: Mn },
                    List: {
                        sydney: function (t) {
                            var e = t.bgColor,
                                n = t.textColor,
                                o = t.linkColor,
                                r = t.borderColor,
                                i = t.widgetBgColor,
                                s = t.iconColor;
                            return {
                                config: {},
                                styles: {
                                    gridPost: { padding: "10px", borderRadius: "10px" },
                                    gridPostContent: { backgroundColor: e, color: n },
                                    gridPostText: { color: n },
                                    gridPostTextLink: { color: o },
                                    gridPostHover: { backgroundColor: e, color: n },
                                    gridPostHoverLink: { color: o },
                                    gridPostIcon: { color: s },
                                    gridPostFooter: { backgroundColor: e },
                                    loadMore: { color: n, backgroundColor: e, borderColor: r },
                                    cssRules: {
                                        ".crt-post-content": { backgroundColor: e },
                                        ".crt-post-text p, .crt-list-post-text-wrap": { color: n },
                                        ".crt-social-icon i": { color: s },
                                        ".crt-list-post, .crt-post": { borderRadius: "1px", borderColor: r },
                                        ".crt-post-content a": { color: o },
                                        ".crt-widget": { backgroundColor: i },
                                    },
                                },
                            };
                        },
                    },
                },
            },
            Wn = Rn;
        !(function (t) {
            (t.WATER_FALL = "Waterfall"), (t.CAROUSEL = "Carousel"), (t.PANEL = "Panel"), (t.GRID = "Grid"), (t.GRID_CAROUSEL = "GridCarousel"), (t.LIST = "List");
        })(Cn || (Cn = {})),
            (function (t) {
                (t.SYDNEY = "sydney"), (t.BERLIN = "berlin"), (t.LONDON = "london"), (t.TOKYO = "tokyo"), (t.NEW_YORK = "new-york");
            })(Pn || (Pn = {}));
        var Gn = { txt: {} };
        Gn.txt.regexen = {};
        var zn = { "&": "&", ">": ">", "<": "<", '"': """, "'": "'" };
        function Vn(t, e) {
            return (
                (e = e || ""),
                "string" != typeof t && (t.global && e.indexOf("g") < 0 && (e += "g"), t.ignoreCase && e.indexOf("i") < 0 && (e += "i"), t.multiline && e.indexOf("m") < 0 && (e += "m"), (t = t.source)),
                new RegExp(
                    t.replace(/#\{(\w+)\}/g, function (t, e) {
                        var n = Gn.txt.regexen[e] || "";
                        return "string" != typeof n && (n = n.source), n;
                    }),
                    e
                )
            );
        }
        (Gn.txt.htmlEscape = function (t) {
            return (
                t &&
                t.replace(/[&"'><]/g, function (t) {
                    return zn[t];
                })
            );
        }),
            (Gn.txt.regexSupplant = Vn),
            (Gn.txt.stringSupplant = function (t, e) {
                return t.replace(/#\{(\w+)\}/g, function (t, n) {
                    return e[n] || "";
                });
            }),
            (Gn.txt.addCharsToCharClass = function (t, e, n) {
                var o = String.fromCharCode(e);
                return n !== e && (o += "-" + String.fromCharCode(n)), t.push(o), t;
            });
        var Un = /A-Za-z\xAA\xB5\xBA\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0-\u08B2\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58\u0C59\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D60\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F4\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16F1-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19C1-\u19C7\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FCC\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA78E\uA790-\uA7AD\uA7B0\uA7B1\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB5F\uAB64\uAB65\uABC0-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC\u0300-\u036F\u0483-\u0489\u0591-\u05BD\u05BF\u05C1\u05C2\u05C4\u05C5\u05C7\u0610-\u061A\u064B-\u065F\u0670\u06D6-\u06DC\u06DF-\u06E4\u06E7\u06E8\u06EA-\u06ED\u0711\u0730-\u074A\u07A6-\u07B0\u07EB-\u07F3\u0816-\u0819\u081B-\u0823\u0825-\u0827\u0829-\u082D\u0859-\u085B\u08E4-\u0903\u093A-\u093C\u093E-\u094F\u0951-\u0957\u0962\u0963\u0981-\u0983\u09BC\u09BE-\u09C4\u09C7\u09C8\u09CB-\u09CD\u09D7\u09E2\u09E3\u0A01-\u0A03\u0A3C\u0A3E-\u0A42\u0A47\u0A48\u0A4B-\u0A4D\u0A51\u0A70\u0A71\u0A75\u0A81-\u0A83\u0ABC\u0ABE-\u0AC5\u0AC7-\u0AC9\u0ACB-\u0ACD\u0AE2\u0AE3\u0B01-\u0B03\u0B3C\u0B3E-\u0B44\u0B47\u0B48\u0B4B-\u0B4D\u0B56\u0B57\u0B62\u0B63\u0B82\u0BBE-\u0BC2\u0BC6-\u0BC8\u0BCA-\u0BCD\u0BD7\u0C00-\u0C03\u0C3E-\u0C44\u0C46-\u0C48\u0C4A-\u0C4D\u0C55\u0C56\u0C62\u0C63\u0C81-\u0C83\u0CBC\u0CBE-\u0CC4\u0CC6-\u0CC8\u0CCA-\u0CCD\u0CD5\u0CD6\u0CE2\u0CE3\u0D01-\u0D03\u0D3E-\u0D44\u0D46-\u0D48\u0D4A-\u0D4D\u0D57\u0D62\u0D63\u0D82\u0D83\u0DCA\u0DCF-\u0DD4\u0DD6\u0DD8-\u0DDF\u0DF2\u0DF3\u0E31\u0E34-\u0E3A\u0E47-\u0E4E\u0EB1\u0EB4-\u0EB9\u0EBB\u0EBC\u0EC8-\u0ECD\u0F18\u0F19\u0F35\u0F37\u0F39\u0F3E\u0F3F\u0F71-\u0F84\u0F86\u0F87\u0F8D-\u0F97\u0F99-\u0FBC\u0FC6\u102B-\u103E\u1056-\u1059\u105E-\u1060\u1062-\u1064\u1067-\u106D\u1071-\u1074\u1082-\u108D\u108F\u109A-\u109D\u135D-\u135F\u1712-\u1714\u1732-\u1734\u1752\u1753\u1772\u1773\u17B4-\u17D3\u17DD\u180B-\u180D\u18A9\u1920-\u192B\u1930-\u193B\u19B0-\u19C0\u19C8\u19C9\u1A17-\u1A1B\u1A55-\u1A5E\u1A60-\u1A7C\u1A7F\u1AB0-\u1ABE\u1B00-\u1B04\u1B34-\u1B44\u1B6B-\u1B73\u1B80-\u1B82\u1BA1-\u1BAD\u1BE6-\u1BF3\u1C24-\u1C37\u1CD0-\u1CD2\u1CD4-\u1CE8\u1CED\u1CF2-\u1CF4\u1CF8\u1CF9\u1DC0-\u1DF5\u1DFC-\u1DFF\u20D0-\u20F0\u2CEF-\u2CF1\u2D7F\u2DE0-\u2DFF\u302A-\u302F\u3099\u309A\uA66F-\uA672\uA674-\uA67D\uA69F\uA6F0\uA6F1\uA802\uA806\uA80B\uA823-\uA827\uA880\uA881\uA8B4-\uA8C4\uA8E0-\uA8F1\uA926-\uA92D\uA947-\uA953\uA980-\uA983\uA9B3-\uA9C0\uA9E5\uAA29-\uAA36\uAA43\uAA4C\uAA4D\uAA7B-\uAA7D\uAAB0\uAAB2-\uAAB4\uAAB7\uAAB8\uAABE\uAABF\uAAC1\uAAEB-\uAAEF\uAAF5\uAAF6\uABE3-\uABEA\uABEC\uABED\uFB1E\uFE00-\uFE0F\uFE20-\uFE2D/
                .source,
            Zn = /0-9\u0660-\u0669\u06F0-\u06F9\u07C0-\u07C9\u0966-\u096F\u09E6-\u09EF\u0A66-\u0A6F\u0AE6-\u0AEF\u0B66-\u0B6F\u0BE6-\u0BEF\u0C66-\u0C6F\u0CE6-\u0CEF\u0D66-\u0D6F\u0DE6-\u0DEF\u0E50-\u0E59\u0ED0-\u0ED9\u0F20-\u0F29\u1040-\u1049\u1090-\u1099\u17E0-\u17E9\u1810-\u1819\u1946-\u194F\u19D0-\u19D9\u1A80-\u1A89\u1A90-\u1A99\u1B50-\u1B59\u1BB0-\u1BB9\u1C40-\u1C49\u1C50-\u1C59\uA620-\uA629\uA8D0-\uA8D9\uA900-\uA909\uA9D0-\uA9D9\uA9F0-\uA9F9\uAA50-\uAA59\uABF0-\uABF9\uFF10-\uFF19/
                .source,
            Xn = /_\u200c\u200d\ua67e\u05be\u05f3\u05f4\uff5e\u301c\u309b\u309c\u30a0\u30fb\u3003\u0f0b\u0f0c\u00b7/.source,
            qn = /\.-/.source;
        (Gn.txt.regexen.hashSigns = /[#?]/),
            (Gn.txt.regexen.hashtagAlpha = new RegExp("[" + Un + "]")),
            (Gn.txt.regexen.hashtagAlphaNumeric = new RegExp("[" + Un + Zn + Xn + qn + "]")),
            (Gn.txt.regexen.endHashtagMatch = Vn(/^(?:#{hashSigns}|:\/\/)/)),
            (Gn.txt.regexen.hashtagBoundary = new RegExp("(?:^|$|[^&" + Un + Zn + Xn + "])")),
            (Gn.txt.regexen.validHashtag = Vn(/[#]+(#{hashtagAlphaNumeric}*)/gi));
        var Kn = Gn,
            Jn = {
                camelize: function (t) {
                    return t.replace(/(?:^|[-_])(\w)/g, function (t, e) {
                        return e ? e.toUpperCase() : "";
                    });
                },
                camelToDash: function (t) {
                    return t.replace(/([A-Z])/g, function (t, e, n) {
                        return (n > 0 ? "-" : "") + t.toLowerCase();
                    });
                },
                twitterLinks: function (t) {
                    return (t = (t = t.replace(/[@]+[A-Za-z0-9-_]+/g, function (t) {
                        var e = t.replace("@", "");
                        return Jn.url("https://twitter.com/" + e, t);
                    })).replace(Kn.txt.regexen.validHashtag, function (t) {
                        var e = t.replace("#", "%23");
                        return Jn.url("https://twitter.com/search?q=" + e, t);
                    }));
                },
                instagramLinks: function (t) {
                    return (t = (t = t.replace(/[@]+[A-Za-z0-9-_\.]+/g, function (t) {
                        var e = t.replace("@", "");
                        return Jn.url("https://www.instagram.com/" + e + "/", t);
                    })).replace(Kn.txt.regexen.validHashtag, function (t) {
                        var e = t.replace("#", "");
                        return Jn.url("https://www.instagram.com/explore/tags/" + e + "/", t);
                    }));
                },
                facebookLinks: function (t) {
                    return (t = (t = t.replace(/[@]+[A-Za-z0-9-_]+/g, function (t) {
                        var e = t.replace("@", "");
                        return Jn.url("https://www.facebook.com/" + e + "/", t);
                    })).replace(/[#]+[A-Za-z0-9-_\u00fc\u00c4\u00e4\u00d6\u00f6\u00dc\u00fc\u00df]+/g, function (t) {
                        var e = t.replace("#", "%23");
                        return Jn.url("https://www.facebook.com/search/top/?q=" + e, t);
                    }));
                },
                removeScripts: function (t, e) {
                    return (
                        void 0 === e && (e = ""),
                        (t = t.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, function () {
                            return e;
                        }))
                    );
                },
                linksToHref: function (t) {
                    return (t = t.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+[A-Za-z0-9-_:%&~\?\/=]+/g, function (t) {
                        return Jn.url(t);
                    }));
                },
                url: function (t, e) {
                    return '<a href="' + t + '" target="_blank" rel="noopener">' + (e = e || t) + "</a>";
                },
                forceHttps: function (t) {
                    return t ? t.replace("http://", "https://") : t;
                },
                youtubeVideoId: function (t) {
                    var e = t.match(/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/);
                    if (e && 11 === e[7].length) return e[7];
                    var n = t.match(/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/))([^#\&\?]*).*/);
                    return !(!n || 11 !== n[6].length) && n[6];
                },
                vimeoVideoId: function (t) {
                    var e = t.match(/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/);
                    return !!(e && e.length >= 2) && e[1];
                },
                filterHtml: function (t) {
                    try {
                        var e = document.createElement("div");
                        return (e.innerHTML = t), e.textContent || e.innerText || "";
                    } catch (e) {
                        return t;
                    }
                },
                isHtml: function (t) {
                    return 0 === t.trim().indexOf("<");
                },
                nl2br: function (t) {
                    return (t = (t = t.trim()).replace(/(?:\r\n|\r|\n)/g, "<br />"));
                },
                replaceAll: function (t, e, n) {
                    return t.split(e).join(n);
                },
            },
            Yn = Jn,
            Qn = {
                debug: !1,
                log: function (t) {
                    window.console && Qn.debug && window.console.log(t);
                },
                error: function (t) {
                    window.console && window.console.error(t);
                },
            },
            to = Qn,
            eo = null;
        function no() {
            return eo.zepto;
        }
        "undefined" != typeof $local ? (eo = $local) : window.$crtZepto ? (eo = window.$crtZepto) : window.Zepto ? (eo = window.Zepto) : window.jQuery && (eo = window.jQuery),
            eo || window.alert("Curator requires jQuery or Zepto. \n\nPlease include jQuery in your HTML before the Curator widget script tag.\n\nVisit http://jquery.com/download/ to get the latest version");
        for (
            var oo = eo,
                ro = 0,
                io = {
                    checkContainer: function (t) {
                        return to.log("Curator->checkContainer: " + t), !!oo(t) || (to.error("Curator could not find the element " + t + ". Please ensure this element existings in your HTML code. Exiting."), !1);
                    },
                    checkPowered: function (t) {
                        //return to.log("Curator->checkPowered"), t.html().indexOf("jug_fury") > 0 || (window.alert("Container is missing Powered by Curator"), !1);
                    },
                    addCSSRule: function (t, e, n, o) {
                        void 0 === o && (o = 0), ("insertRule" in t) ? t.insertRule(e + "{" + n + "}", o) : ("addRule" in t) && t.addRule(e, n);
                    },
                    deleteCSSRules: function (t) {
                        if (t.cssRules) for (var e = t.cssRules.length - 1; e > -1; e--) t.deleteRule(e);
                        else for (var n = 0; n < t.rules.length; n++) t.removeRule(n);
                    },
                    createSheet: function () {
                        var t = document.createElement("style");
                        if ((t.setAttribute("type", "text/css"), t.setAttribute("data-sheet-num", ro + ""), t.appendChild(document.createTextNode("")), document.head.appendChild(t), ro++, !t.sheet))
                            throw new Error("BaseWidget - unable to create stylesheet");
                        return t.sheet;
                    },
                    isTouch: function () {
                        var t = !1;
                        try {
                            t = ("ontouchstart" in document.documentElement);
                        } catch (t) {}
                        return t;
                    },
                    isVisible: function (t) {
                        return "none" !== t.css("display") && "hidden" !== t.css("visibility") && t.width() > 0;
                    },
                },
                so = {
                    filter:
                        '<div class="crt-filter"> \n<div class="crt-filter-networks" ref="networks">\n<ul class="crt-networks" ref="networksUl"> \n    <li class="crt-filter-label"><label><%=this._t(\'filter-network\')%>:</label></li>\n</ul>\n</div> \n<div class="crt-filter-sources" ref="sources">\n<ul class="crt-sources" ref="sourcesUl"> \n    <li class="crt-filter-label"><label><%=this._t(\'filter-source\')%>:</label></li>\n</ul>\n</div> \n</div>',
                    popup:
                        ' \n<div class="crt-popup <%=this.cssClasses()%>"> \n    <a c-on:click-prevent="onClose" class="crt-close crt-icon-cancel"></a> \n    <a c-on:click-prevent="onPrevious" class="crt-previous crt-icon-left-open"></a> \n    <a c-on:click-prevent="onNext" class="crt-next crt-icon-right-open"></a> \n    <div class="crt-popup-left" ref="left">  \n        <div class="crt-video"> \n            <div class="crt-video-container">\n                <video preload="none" ref="video">\n                <source src="<%=video%>" type="video/mp4">\n                </video>\n                <img src="<%=image%>" alt="Image posted by <%=this.userScreenName()%> to <%=this.networkName()%>" />\n                <a c-on:click-prevent="onPlay" class="crt-play"><i class="crt-play-icon"></i></a> \n            </div> \n        </div> \n        <div class="crt-image"> \n            <img src="<%=image%>" alt="Image posted by <%=this.userScreenName()%> to <%=this.networkName()%>" /> \n        </div> \n        <div class="crt-pagination"><ul></ul></div>\n    </div> \n    <div class="crt-popup-right"> \n        <div class="crt-popup-header"> \n            <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n            <img src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>"  /> \n            <div class="crt-post-name"><span><%=user_full_name%></span><br/><a href="<%=this.userUrl()%>" target="_blank"><%=this.userScreenName()%></a></div> \n        </div> \n        <div class="crt-popup-text <%=this.contentTextClasses()%>"> \n            <div class="crt-popup-text-container"> \n                <p class="crt-date"><%=this.dateUrl()%></a></p> \n                <p class="crt-popup-text-title"><%=title%></p> \n                <div class="crt-popup-text-body"><%=this.parseText(text)%></div> \n                \n                <% if (spots_content){ %><div class="crt-popup-post-spots-content"><%=spots_title%><%=spots_content%></div><% } %>\n            </div> \n        </div> \n        <div class="crt-popup-read-more">\n            <a href="<%=url%>" target="_blank" class="crt-button"><%=this._t("go-to-original-post")%></a> \n        </div>\n        <div class="crt-popup-footer">\n            <div class="crt-popup-stats"><span><%=likes%></span> <%=this._t("likes", likes)%> <i class="sep"></i> <span><%=comments%></span> <%=this._t("comments", comments)%></div> \n            <div class="crt-post-share"><span class="ctr-share-hint"></span>\n            <a c-on:click-prevent="onShareFacebookClick" class="crt-share-facebook"><i class="crt-icon-facebook"></i></a>  \n            <a c-on:click-prevent="onShareTwitterClick" class="crt-share-twitter"><i class="crt-icon-twitter"></i></a>\n            </div>\n        </div> \n    </div> \n</div>',
                    "popup-wrapper":
                        '\n<div class="crt-popup-wrapper"> \n    <div class="crt-popup-wrapper-c"> \n        <div class="crt-popup-underlay" ref="underlay" c-on:click-prevent="onUnderlayClick"></div> \n        <div class="crt-popup-container" ref="container"></div> \n    </div> \n</div>',
                    "post-general":
                        ' \n<div class="crt-post <%=this.cssClasses()%>" data-post="<%=id%>"> \n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick">\n        <div class="crt-post-content">\n            <div class="crt-image crt-hitarea crt-post-content-image" > \n                <div class="crt-image-c" ref="imageContainer">\n                    <img src="<%= lazyLoad ? "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw" : image %>" ref="image" class="crt-post-image" alt="<%=this._t("alt-image-posted", {userName:user_screen_name, networkName:network_name})%>" />\n                </div>   \n                <span class="crt-play"><i class="crt-play-icon"></i></span> \n                <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n                <video preload="none" playsinline loop muted ref="video">\n                    <source src="<%=video%>" type="video/mp4">\n                </video>\n            </div> \n            <% if (spots_content){ %><div class="crt-post-spots-content"><%=spots_title%><%=spots_content%></div><% } %>\n            <div class="crt-post-header"> \n                <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=this.postHeading ()%></a></div>\n            </div> \n            <div class="crt-post-title"><%=title%></div> \n            <div class="crt-post-text"><%=this.parseText(text)%></div> \n        </div> \n        <div class="crt-comments-likes">\n            <span class="crt-likes"><%=likes%> <span><%=this._t("likes", likes)%></span></span>  <span class="crt-sep"></span> <span class="crt-comments"><%=comments%> <span><%=this._t("comments", comments)%></span></span> \n        </div>\n        <div class="crt-post-footer"> \n            <img class="crt-post-userimage" src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>" /> \n            <span class="crt-post-username"><a href="<%=this.userUrl()%>" target="_blank"><%=this.userScreenName()%></a></span>\n            <span class="crt-post-date"><%=this.dateUrl()%></span> \n            <div class="crt-post-share">\n                <span class="crt-share-hint"></span>\n                <a class="crt-share-facebook" c-on:click-prevent="onShareFacebookClick()"><i class="crt-icon-facebook"></i></a>  \n                <a class="crt-share-twitter" c-on:click-prevent="onShareTwitterClick()"><i class="crt-icon-twitter"></i></a>\n            </div>\n        </div> \n        <div class="crt-post-max-height-read-more"><a class="crt-post-read-more-button" c-on:click-prevent="onReadMoreClick"><%=this._t("read-more")%></a></div> \n    </div> \n</div>',
                    "post-general-london":
                        ' \n<div class="crt-post crt-post-london crt-post-<%=this.networkIcon()%> <%=this.cssClasses()%>" data-post="<%=id%>"> \n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick">\n        <div class="crt-post-content">\n            <div class="crt-image crt-hitarea crt-post-content-image" > \n                <div class="crt-image-c" ref="imageContainer">\n                    <img src="<%= lazyLoad ? "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw" : image %>" ref="image" class="crt-post-image" alt="<%=this._t("alt-image-posted", {userName:user_screen_name, networkName:network_name})%>" />\n                </div>   \n                <span class="crt-play"><i class="crt-play-icon"></i></span> \n                <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n                <video preload="none" playsinline loop muted ref="video">\n                    <source src="<%=video%>" type="video/mp4">\n                </video>\n            </div> \n            <% if (spots_content){ %><div class="crt-post-spots-content"><%=spots_title%><%=spots_content%></div><% } %>\n            <div class="crt-post-header"> \n                <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=this.postHeading ()%></a></div>\n            </div> \n            <div class="crt-post-title"><%=title%></div> \n            <div class="crt-post-text"><%=this.parseText(text)%></div>\n            <div class="crt-comments-likes">\n                <span class="crt-likes"><%=likes%> <span><%=this._t("likes", likes)%></span></span>  <span class="crt-sep"></span> <span class="crt-comments"><%=comments%> <span><%=this._t("comments", comments)%></span></span> \n            </div>\n        </div>\n        <div class="crt-post-footer"> \n            <img class="crt-post-userimage" src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>" /> \n            <span class="crt-post-username"><a href="<%=this.userUrl()%>" target="_blank"><%=this.userScreenName()%></a></span>\n            <span class="crt-post-date"><%=this.dateUrl()%></span> \n            <div class="crt-post-share">\n                <span class="crt-share-hint"></span>\n                <a class="crt-share-facebook" c-on:click-prevent="onShareFacebookClick()"><i class="crt-icon-facebook"></i></a>  \n                <a class="crt-share-twitter" c-on:click-prevent="onShareTwitterClick()"><i class="crt-icon-twitter"></i></a>\n            </div>\n        </div> \n        <div class="crt-post-max-height-read-more"><a class="crt-post-read-more-button" c-on:click-prevent="onReadMoreClick"><%=this._t("read-more")%></a></div> \n    </div> \n</div>',
                    "post-general-berlin":
                        ' \n<div class="crt-post crt-post-berlin crt-post-<%=this.networkIcon()%> <%=this.cssClasses()%>" data-post="<%=id%>"> \n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick">\n        <div class="crt-post-content">\n            <div class="crt-image crt-hitarea crt-post-content-image" > \n                <div class="crt-image-c" ref="imageContainer">\n                    <img src="<%= lazyLoad ? "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw" : image %>" ref="image" class="crt-post-image" alt="<%=this._t("alt-image-posted", {userName:user_screen_name, networkName:network_name})%>" />\n                </div>   \n                <span class="crt-play"><i class="crt-play-icon"></i></span> \n                <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n                <video preload="none" playsinline loop muted ref="video">\n                    <source src="<%=video%>" type="video/mp4">\n                </video>\n            </div> \n            <% if (spots_content){ %><div class="crt-post-spots-content"><%=spots_title%><%=spots_content%></div><% } %>\n            <div class="crt-post-header"> \n                <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=this.postHeading ()%></a></div>\n            </div> \n            <div class="crt-post-title"><%=title%></div> \n            <div class="crt-post-text"><%=this.parseText(text)%></div> \n            <div class="crt-comments-likes">\n                <span class="crt-likes"><%=likes%> <span><%=this._t("likes", likes)%></span></span>  <span class="crt-sep"></span> <span class="crt-comments"><%=comments%> <span><%=this._t("comments", comments)%></span></span> \n            </div>\n        </div>\n        <div class="crt-post-footer"> \n            <img class="crt-post-userimage" src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>" /> \n            <span class="crt-post-username"><a href="<%=this.userUrl()%>" target="_blank"><%=this.userScreenName()%></a></span>\n            <span class="crt-post-date"><%=this.dateUrl()%></span> \n            <div class="crt-post-share">\n                <span class="crt-share-hint"></span>\n                <a class="crt-share-facebook" c-on:click-prevent="onShareFacebookClick()"><i class="crt-icon-facebook"></i></a>  \n                <a class="crt-share-twitter" c-on:click-prevent="onShareTwitterClick()"><i class="crt-icon-twitter"></i></a>\n            </div>\n        </div> \n        <div class="crt-post-max-height-read-more"><a class="crt-post-read-more-button" c-on:click-prevent="onReadMoreClick"><%=this._t("read-more")%></a></div> \n    </div> \n</div>',
                    "post-grid":
                        '\n<div class="crt-grid-post crt-post-<%=id%> <%=this.cssClasses()%>" data-post="<%=id%>"> \n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick"> \n        <div class="crt-grid-post-content" ref="spacer">\n            <span class="crt-play"><i class="crt-play-icon"></i></span> \n            <span class="crt-social-icon crt-social-icon-normal"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n            <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n            <div class="crt-grid-post-image" style="background-image:url(\'<%=image%>\');"></div> \n            <video preload="none" playsinline loop muted ref="video">\n                <source src="<%=video%>" type="video/mp4">\n            </video>\n            <div class="crt-grid-post-text">\n                <div class="crt-grid-post-text-wrap"> \n                    <div><%=this.parseText(text)%></div> \n                </div>  \n            </div>\n        </div>\n        <div class="crt-post-hover">\n            <div>\n                <div class="crt-post-header"> \n                    <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                    <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=this.postHeading()%></a></div>\n                </div> \n                <div class="crt-post-title"><%=title%></div> \n                <div class="crt-post-text"><%=this.parseText(text)%></div> \n                <div class="crt-post-read-more"><a href="#" class="crt-post-read-more-button"><%=this._t("read-more")%></a></div> \n                <div class="crt-post-footer">\n                    <% if (spots_content){ %><div class="crt-post-spots-content"><%=spots_title%><%=spots_content%></div><% } else { %>\n                    <img class="crt-post-userimage" src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>" /> \n                    <span class="crt-post-username"><a href="<%=this.userUrl()%>" target="_blank"><%=this.userScreenName()%></a></span>\n                    <span class="crt-date"><%=this.prettyDate(source_created_at)%></span> \n                    <div class="crt-post-share">\n                        <span class="crt-share-hint"></span>\n                        <a class="crt-share-facebook" c-on:click-prevent="onShareFacebookClick"><i class="crt-icon-facebook"></i></a>  \n                        <a class="crt-share-twitter" c-on:click-prevent="onShareTwitterClick"><i class="crt-icon-twitter"></i></a>\n                    </div>\n                    <% } %>\n                </div> \n            </div>\n        </div> \n    </div>\n</div>',
                    "post-grid-new-york":
                        '\n<div class="crt-grid-post crt-grid-post-new-york crt-post-<%=id%> <%=this.cssClasses()%>" data-post="<%=id%>">\n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick"> \n        <div class="crt-grid-post-content" ref="spacer">\n            <div class="crt-grid-post-image" style="background-image:url(\'<%=image%>\');"></div> \n            <video preload="none" playsinline loop muted ref="video">\n                <source src="<%=video%>" type="video/mp4">\n            </video>\n            <div class="crt-grid-post-text">\n                <div class="crt-grid-post-text-wrap"> \n                    <div><%=this.parseText(text)%></div> \n                </div> \n            </div>\n            <span class="crt-play"><i class="crt-play-icon"></i></span> \n            <span class="crt-social-icon crt-social-icon-normal"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n            <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n            <span class="crt-date"><%=this.prettyDate(source_created_at)%></span> \n            <div class="crt-post-hover">\n                <div class="crt-post-title"><%=title%></div> \n                <div class="crt-post-text"><%=this.parseText(text)%></div> \n                <div class="crt-post-read-more"><button class="crt-post-read-more-button"><%=this._t("read-more")%></button></div> \n            </div> \n        </div>\n        <div class="crt-post-footer">\n            <img class="crt-post-userimage" src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>" /> \n            <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=this.postHeading ()%></a></div> \n            <span class="crt-post-username"><a href="<%=this.userUrl()%>" target="_blank"><%=this.userScreenName()%></a></span>\n             \n            <div class="crt-post-share">\n                <span class="crt-share-hint"></span>\n                <a class="crt-share-facebook" c-on:click-prevent="onShareFacebookClick"><i class="crt-icon-facebook"></i></a>  \n                <a class="crt-share-twitter" c-on:click-prevent="onShareTwitterClick"><i class="crt-icon-twitter"></i></a>\n            </div>\n        </div>\n    </div>\n</div>',
                    "post-grid-minimal":
                        '\n<div class="crt-grid-post crt-grid-post-v2 crt-post-<%=id%> <%=this.contentImageClasses()%> <%=this.contentTextClasses()%>" data-post="<%=id%>">\n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick"> \n        <div class="crt-post-content"> \n            <div class="crt-hitarea" > \n                <div class="crt-grid-post-spacer" ref="spacer"></div> \n                <div class="crt-grid-post-image">\n                    <div class="crt-post-content-image" style="background-image:url(\'<%=image%>\');"></div> \n                    <span class="crt-play"><i class="crt-play-icon"></i></span> \n                    <span class="crt-social-icon crt-social-icon-normal"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                    <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n                </div>\n                <video preload="none" playsinline loop muted ref="video">\n                    <source src="<%=video%>" type="video/mp4">\n                </video>\n                <div class="crt-grid-post-text">\n                    <div class="crt-grid-post-text-wrap"> \n                        <div><%=this.parseText(text)%></div> \n                    </div> \n                    <span class="crt-social-icon crt-social-icon-normal"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                </div>\n                <div class="crt-post-hover">\n                    <div>\n                        <div class="crt-post-header"> \n                            <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                            <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=this.postHeading ()%></a></div>\n                        </div> \n                    </div>\n                </div> \n            </div> \n        </div> \n    </div>\n</div>',
                    "post-grid-tokyo":
                        '\n<div class="crt-grid-post crt-grid-post-tokyo crt-post-<%=id%> <%=this.cssClasses()%>" data-post="<%=id%>">\n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick"> \n        <div class="crt-grid-post-content" ref="spacer">\n            <span class="crt-play"><i class="crt-play-icon"></i></span> \n            <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n            <div class="crt-grid-post-image" style="background-image:url(\'<%=image%>\');"></div> \n            <video preload="none" playsinline loop muted ref="video">\n                <source src="<%=video%>" type="video/mp4">\n            </video>\n            <div class="crt-grid-post-text">\n                <div class="crt-grid-post-text-wrap"> \n                    <div><%=this.parseText(text)%></div> \n                </div>  \n            </div>\n        </div>\n        <div class="crt-post-hover">\n            <div>\n                <div class="crt-post-header"> \n                    <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                    <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=user_full_name%></a></div>\n                    <div class="crt-date"><%=this.prettyDate(source_created_at)%></div> \n                    <div class="crt-post-share">\n                        <span class="crt-share-hint"></span>\n                        <a class="crt-share-facebook" c-on:click-prevent="onShareFacebookClick"><i class="crt-icon-facebook"></i></a>  \n                        <a class="crt-share-twitter" c-on:click-prevent="onShareTwitterClick"><i class="crt-icon-twitter"></i></a>\n                    </div>\n                </div>\n                <div class="crt-post-footer"></div> \n            </div>\n        </div> \n    </div>\n</div>',
                    "post-grid-retro":
                        ' \n<div class="crt-post-c">\n    <div class="crt-post post<%=id%> <%=this.contentImageClasses()%> <%=this.contentTextClasses()%>"> \n        <div class="crt-post-content"> \n            <div class="crt-hitarea" > \n                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="spacer" alt="<%=this._t("alt-image-posted", {userName:user_screen_name, networkName:network_name})%>" /> \n                <div class="crt-post-content-image" style="background-image:url(\'<%=image%>\');"></div> \n                <div class="crt-post-text-c"> \n                    <div class="crt-post-text"> \n                        <%=this.parseText(text)%> \n                    </div> \n                </div> \n                <a href="javascript:;" class="crt-play"><i class="crt-play-icon"></i></a> \n                <span class="crt-social-icon crt-social-icon-normal"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                <div class="crt-post-hover">\n                    <div class="crt-post-header"> \n                        <img src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>"  /> \n                        <div class="crt-post-name"><span><%=user_full_name%></span><br/><a href="<%=this.userUrl()%>" target="_blank">@<%=user_screen_name%></a></div> \n                    </div> \n                    <div class="crt-post-hover-text"> \n                        <%=this.parseText(text)%> \n                    </div> \n                    <span class="crt-social-icon crt-social-icon-hover"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                </div> \n            </div> \n        </div> \n    </div>\n</div>',
                    "post-list":
                        '\n<div class="crt-list-post crt-post-<%=id%> <%=this.contentImageClasses()%> <%=this.contentTextClasses()%>" data-post="<%=id%>"> \n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick"> \n        <div class="crt-post-content"> \n            <div class="crt-list-post-image" ref="imageCol">\n                <div ref="imageContainer">\n                    <img class="crt-post-content-image" src="<%=image%>" ref="image" alt="<%=this._t("alt-image-posted", {userName:user_screen_name, networkName:network_name})%>" /> \n                    <a href="javascript:;" class="crt-play"><i class="crt-play-icon"></i></a> \n                    <span class="crt-social-icon crt-social-icon-normal"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                    <span class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></span>\n                </div> \n            </div>\n            <div class="crt-list-post-text">\n                <div class="crt-post-header"> \n                    <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=this.postHeading()%></a></div>\n                </div> \n                <div class="crt-list-post-text-wrap"> \n                    <div class="crt-post-title"><%=title%></div> \n                    <div><%=this.parseText(text)%></div> \n                    <div class="crt-comments-likes">\n                        <span class="crt-likes"><%=likes%> <span><%=this._t("likes", likes)%></span></span> <span class="crt-sep"></span> <span class="crt-comments"><%=comments%> <span><%=this._t("comments", comments)%></span></span> \n                    </div>\n                </div> \n                 <div class="crt-post-footer">\n                    <img class="crt-post-userimage" src="<%=user_image%>" alt="<%=this._t("alt-profile-image",{userName:user_full_name})%>" /> \n                    <span class="crt-post-username"><a href="<%=this.userUrl()%>" target="_blank">@<%=user_screen_name%></a></span>\n                    <span class="crt-post-date"><%=this.dateUrl()%></span> \n                    <div class="crt-post-share">\n                        <span class="crt-share-hint"></span>\n                        <a class="crt-share-facebook" c-on:click-prevent="onShareFacebookClick()"><i class="crt-icon-facebook"></i></a>  \n                        <a class="crt-share-twitter" c-on:click-prevent="onShareTwitterClick()"><i class="crt-icon-twitter"></i></a>\n                    </div>\n                </div>  \n            </div>\n        </div> \n    </div>\n</div>',
                    "ad-general":
                        ' \n<div class="crt-post crt-ad ctr-ad-<%=slug%>" data-post="<%=slug%>"> \n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick">\n        <div class="crt-post-content">\n            <div class="crt-image crt-hitarea crt-post-content-image" > \n                <div class="crt-image-c" ref="imageContainer">\n                    <img src="<%= lazyLoad ? "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw" : image %>" ref="image" class="crt-post-image" alt="Image posted by to <%=this.networkName()%>" />\n                </div>   \n                <span class="crt-play"><i class="crt-play-icon"></i></span> \n                <div class="crt-image-carousel"><i class="crt-icon-image-carousel"></i></div> \n            </div> \n            <div class="crt-post-header"> \n                <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                <div class="crt-post-fullname"><a href="<%=this.userUrl()%>" target="_blank"><%=title%></a></div>\n            </div> \n            <div class="crt-post-text" ref="text">-</div> \n        </div> \n    </div> \n</div>',
                    "ad-grid":
                        '\n<div class="crt-grid-post crt-grid-ad ctr-ad-<%=slug%>" data-add-slug="<%=slug%>">\n    <div class="crt-post-c" ref="postC" c-on:click="onPostClick"> \n        <div class="crt-grid-post-content" ref="spacer">\n            <span class="crt-social-icon crt-social-icon-normal"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n            <div class="crt-grid-post-image" style="background-image:url(\'<%=image%>\');"></div> \n            <div class="crt-grid-post-text">\n                <div class="crt-grid-post-text-wrap"> \n                    <div><%=this.parseText(text)%></div> \n                </div>  \n            </div>\n        </div>\n        <div class="crt-post-hover">\n            <div>\n                <div class="crt-post-header"> \n                    <span class="crt-social-icon"><i class="crt-icon-<%=this.networkIcon()%>"></i></span> \n                    <div class="crt-post-fullname"><a href="<%=this.url%>" target="_blank"><%=title%></a></div>\n                </div> \n                <div class="crt-post-text"> \n                    <%=this.parseText(text)%> \n                </div> \n            </div>\n        </div> \n    </div>\n</div>',
                    "widget-panel":
                        '\n<div class="crt-carousel-feed">\n<div class="crt-carousel-stage" ref="stage">\n<div class="crt-carousel-slider" ref="slider">\n\n</div>\n</div>\n\n<button c-on:click-prevent="onPrevClick" type="button" data-role="none" class="crt-panel-prev crt-panel-arrow" aria-label="Previous" role="button" aria-disabled="false">Previous</button>\n<button c-on:click-prevent="onNextClick" type="button" data-role="none" class="crt-panel-next crt-panel-arrow" aria-label="Next" role="button" aria-disabled="false">Next</button>\n\n</div>\n',
                    "widget-carousel":
                        '\n<div class="crt-carousel-feed">\n<div class="crt-carousel-stage" ref="stage">\n<div class="crt-carousel-slider" ref="slider">\n\n</div>\n</div>\n\n<button c-on:click-prevent="onPrevClick" type="button" data-role="none" class="crt-panel-prev crt-panel-arrow" aria-label="Previous" role="button" aria-disabled="false">Previous</button>\n<button c-on:click-prevent="onNextClick" type="button" data-role="none" class="crt-panel-next crt-panel-arrow" aria-label="Next" role="button" aria-disabled="false">Next</button>\n\n</div>\n',
                    "widget-waterfall":
                        '\n<div class="crt-feed-scroll">\n    <div class="crt-feed" ref="feed">\n        <div class="crt-feed-spacer"> -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- </div>\n    </div>\n    <div class="crt-load-more-container" ref="loadMore"><button c-on:click-prevent="onMoreClick" class="crt-load-more"><span><%=this._t("load-more")%></span></button></div>\n</div>\n',
                    "widget-grid":
                        '\n<div>\n<div class="crt-feed-window" ref="feedWindow">\n    <div class="crt-feed" ref="feed"></div>\n</div>\n<div class="crt-load-more-container" ref="loadMore"><button c-on:click-prevent="onMoreClick" class="crt-load-more"><span><%=this._t("load-more")%></span></button></div>\n</div>\n',
                    "widget-grid-carousel":
                        '\n<div class="crt-grid-carousel-feed">\n<div class="crt-grid-carousel-stage" ref="stage">\n<div class="crt-grid-carousel-slider" ref="slider">\n\n</div>\n</div>\n\n<button c-on:click-prevent="onPrevClick" type="button" data-role="none" class="crt-panel-prev crt-panel-arrow" aria-label="Previous" role="button" aria-disabled="false">Previous</button>\n<button c-on:click-prevent="onNextClick" type="button" data-role="none" class="crt-panel-next crt-panel-arrow" aria-label="Next" role="button" aria-disabled="false">Next</button>\n\n</div>\n',
                    "widget-list":
                        '\n<div class="crt-feed-window">\n    <div class="crt-feed" ref="feed"></div>\n    <div class="crt-load-more-container" ref="loadMore"><button c-on:click-prevent="onMoreClick" class="crt-load-more"><span><%=this._t("load-more")%></span></button></div>\n</div>',
                },
                ao = function (t) {
                    return !isNaN(parseFloat(t)) && isFinite(t);
                },
                co = function (t) {
                    return "object" == typeof t && null !== t;
                },
                uo = {
                    getTranslationFunction: function (t, e) {
                        var n = (e = co(e) ? e : {}).debug,
                            o = e.namespaceSplitter || "::";
                        function r(e) {
                            if (t[e]) return t[e];
                            var n = e.split(o),
                                r = n[0],
                                i = n[1];
                            return t[r] && t[r][i] ? t[r][i] : null;
                        }
                        function i(t, e) {
                            if (co(t)) {
                                var o,
                                    r = Object.keys(t);
                                if (0 === r.length) return n && window.console.log("[Translation] No plural forms found."), null;
                                for (var i = 0; i < r.length; i++) 0 === r[i].indexOf("gt") && (o = parseInt(r[i].replace("gt", ""), 10));
                                t[e] ? (t = t[e]) : e > o ? (t = t["gt" + o]) : t.n ? (t = t.n) : (n && window.console.log('[Translation] No plural forms found for count:"' + e + '" in', t), (t = t[Object.keys(t).reverse()[0]]));
                            }
                            return t;
                        }
                        function s(t, e) {
                            return (
                                (o = t),
                                "[object String]" === Object.prototype.toString.call(o)
                                    ? t.replace(/\{(\w*)\}/g, function (t, o) {
                                          return e.hasOwnProperty(o) ? (e.hasOwnProperty(o) ? e[o] : o) : (n && window.console.log('Could not find replacement "' + o + '" in provided replacements object:', e), "{" + o + "}");
                                      })
                                    : t
                            );
                            var o;
                        }
                        return function (t) {
                            var e = co(arguments[1]) ? arguments[1] : co(arguments[2]) ? arguments[2] : {},
                                o = ao(arguments[1]) ? arguments[1] : ao(arguments[2]) ? arguments[2] : null,
                                a = r(t);
                            return null !== o && ((e.n = e.n ? e.n : o), (a = i(a, o))), null === (a = s(a, e)) && ((a = n ? "@@" + t + "@@" : t), n && window.console.log('Translation for "' + t + '" not found.')), a;
                        };
                    },
                },
                lo = {},
                po = "\nid,en,da,de,it,nl,es,fr,po,ru,sl,fi,pl,ar,zh,jp,pt-br,kr,th\nload-more,Load more,Hent mere,Mehr anzeigen,Di pi�,Laad meer,Cargar m�s,Voir plus,Carregar Mais,????????? ??????,Prika�i ve?,Lataa lis��,,????? ??????,????,?????,Carregar mais,? ??,?????????\nminutes-ago.1,{n} minute ago,{n} minut siden,Vor einer Minute,Un minuto fa,{n} minuut geleden,Hace un minuto,Il y a {n} minute,Tem um minuto,???? ?????? ?????,pred {n} minuto,{n} minuutti sitten,,??? {n} ?? ???????,{n}???,{n} ??,h� {n} minuto,{n} ? ??,???????????\nminutes-ago.n,{n} minutes ago,{n} minutter siden,Vor {n} Minuten,{n} minuti fa,{n} minuten geleden,Hace {n} minutos,Il y a {n} minutes,Tem {n} minutos,{n} ????? ?????,pred {n} minutami,{n} minuuttia sitten,,??? {n} ?? ???????,{n}???,{n} ??,h� {n} minutos,{n} ? ??,???????????????\nhours-ago.1,{n} hour ago,{n} time siden,Vor einer Stunde,Un'ora fa,{n} uur geleden,Hace una hora,Il y a {n} heure,Tem {n} hora,???? ??? ?????,pred {n} uro,{n} tunti sitten,,??? {n} ?? ???????,{n}???,{n} ???,h� {n} hora,{n} ?? ??,??????????????\nhours-ago.n,{n} hours ago,{n} timer siden,Vor {n} Stunden,{n} ore fa,{n} uren geleden,Hace {n} horas,Il y a {n} heures,Tem {n} horas,{n} ????? ?????,pred {n} urami,{n} tuntia sitten,,??? {n} ?? ???????,{n}???,{n} ???,h� {n} horas,{n} ?? ??,??????????????????\ndays-ago.1,{n} day ago,{n} dag siden,Vor einem Tag,Un giorno fa,{n} dag geleden,Hace un d�a,Il y a {n} jour,Faz um dia,???? ???? ?????,pred {n} dnevom,{n} p�iv� sitten,,???{n} ?? ??????,{n}??,{n} ??,h� {n} dia,{n} ? ??,???????\ndays-ago.n,{n} days ago,{n} dage siden,Vor {n} Tagen,{n} giorni fa,{n} dagen geleden,Hace {n} d�as,Il y a {n} jours,Fazem {n} dias,{n} ???? ?????,pred {n} dnevi,{n} p�iv�� sitten,,???{n} ?? ??????,{n}??,{n} ??,h� {n} dias,{n} ? ??,???????????\nweeks-ago.1,{n} week ago,{n} uge siden,Vor einer Woche,Una settimana fa,{n} week geleden,Hace una semana,Il y a {n} semaine,Faz uma semana,???? ?????? ?????,pred {n} tednom,{n} viikko sitten,,??? {n} ?? ????????,{n}??,{n} ???,h� {n} semana,{n} ? ??,???????????\nweeks-ago.n,{n} weeks ago,{n} uger siden,Vor {n} Wochen,{n} settimane fa,{n} weken geleden,Hace {n} semanas,Il y a {n} semaines,Fazem {n} semanas,{n} ?????? ?????,pred {n} tedni,{n} viikkoa sitten,,??? {n} ?? ????????,{n}??,{n} ???,h� {n} semanas,{n} ? ??,???????????????\nmonths-ago.1,{n} month ago,{n} m�ned siden,Vor einem Monat,Un mese fa,{n} maand geleden,Hace un mes,Il y a {n} mois,Tem um m�s,???? ????? ?????,pred {n} mesecem,{n} kuukausi sitten,,??? {n} ?? ??????,{n}???,{n} ???,h� {n} m�s,{n} ? ??,?????????\nmonths-ago.n,{n} months ago,{n} m�neder siden,Vor {n} Monaten,{n} mesi,{n} maanden geleden,Hace {n} meses,Il y a {n} mois,Tem {n} meses,{n} ??????? ?????,pred {n} meseci,{n} kuukautta sitten,,??? {n} ?? ??????,{n}???,{n} ???,h� {n} meses,{n} ? ??,?????????????\nyesterday,Yesterday,I g�r,Gestern,Ieri,Gisteren,Ayer,Hier,Ontem,?????,V?eraj,Eilen,,???????,??,??,Ontem,??,????????\njust-now,Just now,Lige nu,Eben,Appena,Nu,Ahora,Il y a un instant,Agora,?????? ???,Pravkar,Juuri nyt,,????,??,????,Agora,??,????????????\nprevious,Previous,Forrige,Zur�ck,Indietro,Vorige,Anterior,Pr�c�dent,Anterior,??????????,Prej�nji,Edellinen,,??????,???,????,Anterior,??,????????\nnext,Next,N�ste,Weiter,Pi�,Volgende,Siguiente,Suivant,Pr�ximo,?????????,Naslednji,Seuraava,,??????,???,????,Pr�ximo,??,?????\ncomments,Comments,Kommentarer,Kommentare,Commenti,Comments,Comentarios,Commentaires,Coment�rios,???????????,Komentarji,Kommenttia,,???????,??,????,Coment�rios,??,???????????\nlikes,Likes,Synes godt om,Gef�llt mir,Mi piace,Likes,Me gusta,J'aime,Curtir,?????,V�e?ki,Tykk�yst�,,???????,?,???,Curtidas,???,?????\nread-more,Read more,L�s mere,Weiterlesen,Di pi�,Lees meer,Leer m�s,En savoir plus,Leia mais,?????????,Preberi ve?,Lue lis��,,????? ??????,????,?????,Leia mais,? ????,?????????????\nfilter,Filter,Filter,Filtern,filtrare,Filtreren,filtrar,filtrer,Filtro,???????????,Filter,Suodata,,?????,??,?????,Filtro,??,???????\nall,All,Alle,Alle,Tutti,Alle,Todas,Tout,Todos,???,Vsi,Kaikki,,??????,??,??,Tudo,??,???????\ngo-to-original-post,Go to original post,G� til originalt oplsag,Original-Post ansehen,,Ga naar het bericht,Ver publicaci�n original,Voir la publication,,,,N�yt� alkuper�inen viesti,,?????? ??????? ??????,????,?????,Ir para o conte�do original,??? ???? ??,?????????????????\nfilter-source,Filter source,Filter kilde,Filtern,Filtrare,Filtreren,Filtrar,Filtrer,Filtro,???????????,Filter,Suodata l�hde,,????? ???? ??????,???,????????,Filtro por fonte,?? ??,?????????????????\nfilter-network,Filter network,Filter netv�rk,Filtern,Filtrare,Filtreren,Filtrar,Filtrer,Filtro,???????????,Filter,Suodata palvelu,,????? ???? ??????,????,???????????,Filtro por rede social,?? ????,????????????????\nno-posts,The feed contains no posts,Der er ingen opslag i dette feed,Der Feed enth�lt keine Posts,,,,,,,,Sy�tteess� ei ole sis�lt��,,??? ?? ???????? ?? ???????,???????????,????????????????,Este feed est� vazio,??? ???? ????,??????????\nalt-image-posted,Image posted by {userName} to {networkName},Billede sl�et op af {userName} p� {networkName},,,,,,,,,{networkName} kuva lis�tty k�ytt�j�n {userName} toimesta,,????? ?????? ?? ??? ???????? {userName} ??? ?????? {networkName},???{userName}???{networkName},,Imagem publicada por {userName} no {networkName},{userName} ?  {networkName} ? ???? ???,?????????????? (??????????) ????? (?????????????)\nalt-profile-image,Profile image for {userName},Profilbillede for {userName},,,,,,,,,K�ytt�j�n {userName} profiilikuva,,???? ??? ???????? {userName},{userName}?????,,Imagem de perfil para {userName},{userName} ? ??? ???,????????????????? (??????????)".split(
                    "\n"
                ),
                ho = po.length - 1;
            ho >= 0;
            ho--
        )
            po[ho] || po.splice(ho, 1);
        var fo = po[0].split(",");
        function go(t, e, n) {
            for (var o = e.split("."), r = 0; r < o.length; r++) {
                var i = o[r];
                t[i] || (t[i] = {}), r === o.length - 1 ? (t[i] = n) : (t = t[i]);
            }
        }
        for (ho = 1; ho < po.length; ho++) for (var vo = po[ho].split(","), mo = 1; mo < vo.length; mo++) go(lo, fo[mo] + "." + vo[0], vo[mo]);
        var yo = lo,
            wo = {},
            _o = "en",
            bo = {
                setLang: function (t) {
                    _o = t;
                },
                t: function (t, e, n) {
                    void 0 === e && (e = 0),
                        wo[(n = n || _o)] || (yo[n] ? (wo[n] = uo.getTranslationFunction(yo[n])) : (window.console.error("Unsupported language `" + n + "`"), (wo[n] = uo.getTranslationFunction(yo.en)))),
                        (t = (t = t.toLowerCase()).replace(" ", "-"));
                    var o = wo[n](t, e);
                    return o === t ? bo.t(t, e, "en") : o;
                },
            },
            Ao = bo,
            Co = null;
        var Po = {
                dateFromString: function (t) {
                    var e = t.replace(/\D/g, " ").split(" "),
                        n = +e[0],
                        o = +e[1];
                    o--;
                    var r = +e[2],
                        i = +e[3],
                        s = +e[4],
                        a = +e[5];
                    return new Date(Date.UTC(n, o, r, i, s, a));
                },
                dateAsDayMonthYear: function (t) {
                    var e = new Date(parseInt(t, 10)),
                        n = e.getDate() + "",
                        o = e.getMonth() + 1 + "",
                        r = e.getFullYear() + "";
                    return (n = 1 === n.length ? "0" + n : n) + "/" + (o = 1 === o.length ? "0" + o : o) + "/" + r;
                },
                dateAsTimeArray: function (t) {
                    var e,
                        n = new Date(parseInt(t, 10)),
                        o = n.getHours() + "",
                        r = n.getMinutes() + "";
                    return (
                        n.getHours() >= 12 ? ((e = "PM"), n.getHours() > 12 && (o = n.getHours() - 12 + "")) : (e = "AM"),
                        (o = 1 === o.length ? "0" + o : o),
                        (r = 1 === r.length ? "0" + r : r),
                        [parseInt(o.charAt(0), 10), parseInt(o.charAt(1), 10), parseInt(r.charAt(0), 10), parseInt(r.charAt(1), 10), e]
                    );
                },
                fuzzyDate: function (t) {
                    var e = Date.parse(t + " UTC"),
                        n = new Date(),
                        o = Math.round((n.getTime() - e) / 1e3);
                    return o < 30
                        ? "Just now"
                        : o < 60
                        ? o + " seconds ago"
                        : o < 120
                        ? "a minute ago."
                        : o < 3600
                        ? Math.floor(o / 60) + " minutes ago"
                        : 1 === Math.floor(o / 3600)
                        ? "1 hour ago."
                        : o < 86400
                        ? Math.floor(o / 3600) + " hours ago"
                        : o < 172800
                        ? "Yesterday"
                        : o < 604800
                        ? "This week"
                        : o < 1209600
                        ? "Last week"
                        : o < 2592e3
                        ? "This month"
                        : e + "";
                },
                prettyDate: function (t) {
                    var e = Po.dateFromString(t),
                        n = (new Date().getTime() - e.getTime()) / 1e3,
                        o = Math.floor(n / 86400);
                    if (isNaN(o) || o < 0 || o >= 31)
                        return (function () {
                            if (null !== Co) return Co;
                            Co = !1;
                            try {
                                new Date().toLocaleDateString("i");
                            } catch (t) {
                                Co = "RangeError" === t.name;
                            }
                            return Co;
                        })()
                            ? e.toLocaleDateString(void 0, { month: "short", day: "numeric", year: "numeric" })
                            : this.shortDate(e);
                    var r = Math.floor(n / 60),
                        i = Math.floor(n / 3600),
                        s = Math.ceil(o / 7);
                    return (
                        (0 === o && ((n < 60 && Ao.t("just-now")) || (n < 3600 && Ao.t("minutes ago", r)) || (n < 86400 && Ao.t("hours ago", i)))) ||
                        (1 === o && Ao.t("yesterday")) ||
                        (o < 7 && Ao.t("days-ago", o)) ||
                        (o < 31 && Ao.t("weeks ago", s))
                    );
                },
                shortDate: function (t) {
                    var e = t.getFullYear(),
                        n = t.getMonth() + 1,
                        o = t.getDate(),
                        r = e.toString(),
                        i = ["-", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][n];
                    return o.toString() + " " + i + " " + r;
                },
            },
            Eo = Po,
            ko = {
                data: {},
                get: function (t, e) {
                    void 0 === e && (e = "");
                    var n = e;
                    if (this.data.data.length > 0)
                        for (var o = 0, r = this.data.data; o < r.length; o++) {
                            var i = r[o];
                            if (i.name === t) return i.value;
                        }
                    return n;
                },
                networkIcon: function () {
                    return this.data.network_name ? this.data.network_name.toLowerCase() : "";
                },
                networkName: function () {
                    return this.data.network_name ? this.data.network_name.toLowerCase() : "";
                },
                userUrl: function () {
                    if (this.data.user_url && "" !== this.data.user_url) return this.data.user_url;
                    if (this.data.originator_user_url && "" !== this.data.originator_user_url) return this.data.originator_user_url;
                    if (this.data.userUrl && "" !== this.data.userUrl) return this.data.userUrl;
                    if (this.data.user_screen_name) {
                        var t = this.data.network_id + "";
                        if ("1" === t) return "http://twitter.com/" + this.data.user_screen_name;
                        if ("2" === t) return "http://instagram.com/" + this.data.user_screen_name;
                        if ("3" === t) return "http://facebook.com/" + this.data.user_screen_name;
                    }
                    return "#";
                },
                userScreenName: function () {
                    return "RSS" === this.data.network_name ? (this.data.user_screen_name ? this.data.user_screen_name : "") : this.data.user_screen_name ? "@" + this.data.user_screen_name : "";
                },
                postHeading: function () {
                    return "RSS" === this.data.network_name ? this.get("title", "") : this.data.user_full_name;
                },
                parseText: function (t, e) {
                    return (
                        void 0 === e && (e = !0),
                        e && (t = Yn.removeScripts(t)),
                        this.data.is_html
                            ? t
                            : ("Twitter" === this.data.network_name
                                  ? ((t = Yn.linksToHref(t)), (t = Yn.twitterLinks(t)))
                                  : "Instagram" === this.data.network_name
                                  ? ((t = Yn.linksToHref(t)), (t = Yn.instagramLinks(t)))
                                  : "Facebook" === this.data.network_name
                                  ? ((t = Yn.linksToHref(t)), (t = Yn.facebookLinks(t)))
                                  : (t = Yn.linksToHref(t)),
                              Yn.nl2br(t))
                    );
                },
                nl2br: function (t) {
                    return Yn.nl2br(t);
                },
                contentImageClasses: function () {
                    return this.data.image ? "crt-post-has-image" : "crt-post-content-image-hidden crt-post-no-image";
                },
                contentTextClasses: function () {
                    return this.data.text ? "crt-post-has-text" : "crt-post-content-text-hidden crt-post-no-text";
                },
                cssClasses: function () {
                    return this.data._classes ? this.data._classes.join(" ") : "";
                },
                fuzzyDate: function (t) {
                    return Eo.fuzzyDate(t);
                },
                prettyDate: function (t) {
                    var e = Eo.prettyDate(t);
                    return e || "";
                },
                dateUrl: function () {
                    var t = this.data.url,
                        e = this.data.source_created_at;
                    return t ? '<a href="' + t + '" target="_blank">' + ko.prettyDate(e) + "</a>" : ko.prettyDate(e);
                },
                _t: function (t, e) {
                    return void 0 === e && (e = 0), Ao.t(t, e);
                },
                _s: function (t) {
                    return "string" == typeof t ? Yn.removeScripts(t) : "";
                },
            },
            Oo = {},
            To = ko,
            So = {
                renderTemplate: function (t, e, n) {
                    return n && (e.options = n), So.renderDiv(t, e);
                },
                renderDiv: function (t, e) {
                    var n = So.render(t, e);
                    return oo(n).filter("div");
                },
                loadTemplate: function (t) {
                    var e = void 0,
                        n = "";
                    t = t.trim();
                    try {
                        if ((n = oo("script#" + t)).length > 0) return (e = n.html());
                    } catch (t) {}
                    if ((void 0 !== so[t] ? (e = so[t]) : Yn.isHtml(t) && (e = t), "string" != typeof e)) throw new Error("Could not find template `" + t + "`");
                    return e;
                },
                render: function (t, e) {
                    var n = Oo[t];
                    if (!n) {
                        var o = this.loadTemplate(t)
                            .replace(/[\r\t\n]/g, " ")
                            .replace(/'(?=[^%]*%>)/g, "\t")
                            .split("'")
                            .join("\\'")
                            .split("\t")
                            .join("'")
                            .replace(/<%=(.+?)%>/g, "',this._s($1),'")
                            .replace(/<!=(.+?)!>/g, "',$1,'")
                            .split("<%")
                            .join("');")
                            .split("%>")
                            .join("p.push('");
                        (n = new Function("obj", "var p=[],print=function(){p.push.apply(p,arguments);};with(obj){p.push('" + o + "');}return p.join('');")), (Oo[t] = n);
                    }
                    try {
                        return (To.data = e), n.call(To, e);
                    } catch (n) {
                        throw (to.error("Template render error: " + n.message + " for template: " + t), window.console.log(e), new Error("Template render error: " + n.message + " for template: " + t));
                    }
                },
            },
            Do = So,
            Fo = {
                FEED_LOADED: "feed:loaded",
                FEED_FAILED: "feed:failed",
                FILTER_CHANGED: "filter:changed",
                POSTS_LOADED: "posts:loaded",
                POSTS_FAILED: "posts:failed",
                POSTS_RENDERED: "posts:rendered",
                AD_CREATED: "ad:created",
                POST_CREATED: "post:created",
                POST_CLICK: "post:click",
                POST_CLICK_READ_MORE: "post:clickReadMore",
                POST_IMAGE_LOADED: "post:imageLoaded",
                POST_IMAGE_FAILED: "post:imageFailed",
                POST_LAYOUT_CHANGED: "post:layoutChanged",
                CAROUSEL_CHANGED: "carousel:changed",
                GRID_HEIGHT_CHANGED: "grid:heightChanged",
                PANE_HEIGHT_CHANGED: "pane:heightChanged",
                WIDGET_RESIZE: "widget:resize",
            },
            Lo = n(2),
            xo = function () {
                return w.a.Date.now();
            };
        var Io = function (t) {
                return "symbol" == typeof t || (Ct(t) && "[object Symbol]" == S(t));
            },
            jo = /^\s+|\s+$/g,
            Bo = /^[-+]0x[0-9a-f]+$/i,
            $o = /^0b[01]+$/i,
            No = /^0o[0-7]+$/i,
            Ho = parseInt;
        var Mo = function (t) {
                if ("number" == typeof t) return t;
                if (Io(t)) return NaN;
                if (D(t)) {
                    var e = "function" == typeof t.valueOf ? t.valueOf() : t;
                    t = D(e) ? e + "" : e;
                }
                if ("string" != typeof t) return 0 === t ? t : +t;
                t = t.replace(jo, "");
                var n = $o.test(t);
                return n || No.test(t) ? Ho(t.slice(2), n ? 2 : 8) : Bo.test(t) ? NaN : +t;
            },
            Ro = Math.max,
            Wo = Math.min;
        var Go = function (t, e, n) {
                var o,
                    r,
                    i,
                    s,
                    a,
                    c,
                    u = 0,
                    l = !1,
                    d = !1,
                    p = !0;
                if ("function" != typeof t) throw new TypeError("Expected a function");
                function h(e) {
                    var n = o,
                        i = r;
                    return (o = r = void 0), (u = e), (s = t.apply(i, n));
                }
                function f(t) {
                    return (u = t), (a = setTimeout(v, e)), l ? h(t) : s;
                }
                function g(t) {
                    var n = t - c;
                    return void 0 === c || n >= e || n < 0 || (d && t - u >= i);
                }
                function v() {
                    var t = xo();
                    if (g(t)) return m(t);
                    a = setTimeout(
                        v,
                        (function (t) {
                            var n = e - (t - c);
                            return d ? Wo(n, i - (t - u)) : n;
                        })(t)
                    );
                }
                function m(t) {
                    return (a = void 0), p && o ? h(t) : ((o = r = void 0), s);
                }
                function y() {
                    var t = xo(),
                        n = g(t);
                    if (((o = arguments), (r = this), (c = t), n)) {
                        if (void 0 === a) return f(c);
                        if (d) return clearTimeout(a), (a = setTimeout(v, e)), h(c);
                    }
                    return void 0 === a && (a = setTimeout(v, e)), s;
                }
                return (
                    (e = Mo(e) || 0),
                    D(n) && ((l = !!n.leading), (i = (d = "maxWait" in n) ? Ro(Mo(n.maxWait) || 0, e) : i), (p = "trailing" in n ? !!n.trailing : p)),
                    (y.cancel = function () {
                        void 0 !== a && clearTimeout(a), (u = 0), (o = c = r = a = void 0);
                    }),
                    (y.flush = function () {
                        return void 0 === a ? s : m(xo());
                    }),
                    y
                );
            },
            zo = (function () {
                function t(t, e, n) {
                    (this.items = []),
                        (this.layout = t),
                        (this.$el = oo("<div></div>")
                            .addClass("galcolumn")
                            .addClass("crt-col-" + n)
                            .css({ width: e + "%", float: "left", "-webkit-box-sizing": "border-box", "-moz-box-sizing": "border-box", "-o-box-sizing": "border-box", "box-sizing": "border-box" }));
                }
                return (
                    (t.prototype.setWidth = function (t) {
                        this.$el.css({ width: t + "%" });
                    }),
                    (t.prototype.colHeight = function () {
                        return this.$el.height();
                    }),
                    (t.prototype.append = function (t) {
                        this.$el.append(t.$el);
                    }),
                    (t.prototype.empty = function () {
                        for (var t = 0; t < this.items.length; t++) this.items[t].$el.remove();
                        this.items = [];
                    }),
                    (t.prototype.destroy = function () {
                        this.$el.remove();
                    }),
                    t
                );
            })(),
            Vo = {
                postUrl: function (t) {
                    return t.url && "" !== t.url && "''" !== t.url ? t.url : t.network_id + "" == "1" ? "https://twitter.com/" + t.user_screen_name + "/status/" + t.source_identifier : "";
                },
                center: function (t, e, n) {
                    var o = window.screen,
                        r = n || {},
                        i = r.height || o.height,
                        s = r.width || o.height;
                    return { top: i ? (i - e) / 2 : 0, left: s ? (s - t) / 2 : 0 };
                },
                popup: function (t, e, n, o, r) {
                    var i = this.center(n, o),
                        s = "height=" + o + ",width=" + n + ",top=" + i.top + ",left=" + i.left + ",scrollbars=" + r + ",resizable";
                    window.open(t, e, s);
                },
                tinyparser: function (t, e) {
                    return t.replace(/\{\{(.*?)\}\}/g, function (t, n) {
                        return e && void 0 !== e[n] ? encodeURIComponent(e[n]) : "";
                    });
                },
                debounce: function (t, e, n, o) {
                    var r;
                    function i() {
                        var i = o && !r;
                        window.clearTimeout(r),
                            (r = window.setTimeout(function () {
                                o || t.apply(n);
                            }, e)),
                            i && t.apply(n);
                    }
                    return (
                        (i.cancel = function () {
                            void 0 !== r && window.clearTimeout(r), (r = void 0);
                        }),
                        i
                    );
                },
                uId: function () {
                    return "_" + Math.random().toString(36).substr(2, 9);
                },
            },
            Uo = { selector: ".crt-post-c", animate: !0, animationSpeed: 200, animationDuration: 300, animationEffect: "fadeInOnAppear", animationQueue: !0 },
            Zo = (function () {
                function t(t, e) {
                    (this.previousWidth = -1),
                        (this.isResizing = !1),
                        (this.visible = !1),
                        (this.colWidth = -1),
                        (this.colCount = -1),
                        (this.columns = []),
                        (this.items = []),
                        this.log("constructor"),
                        (this.box = e),
                        (this.widget = t),
                        (this._layoutWaterfallConfig = Uo),
                        (this.id = Vo.uId()),
                        this.resize(),
                        (this.ro = this.createHandlers()),
                        (this.$spacer = this.box.find(".crt-feed-spacer")),
                        this.$spacer.remove();
                }
                return (
                    (t.prototype.createHandlers = function () {
                        var t = this;
                        this.log("createHandlers");
                        var e = Go(this.resize.bind(this), 100),
                            n = new Lo.a(function (n) {
                                n.length > 0 && (t.previousWidth <= 0 ? t.resize() : e());
                            });
                        return n.observe(this.box[0]), n;
                    }),
                    (t.prototype._setCols = function (t) {
                        this.log("_setCols " + t);
                        var e = this.box.width();
                        this.log("boxWidth: " + e), (this.colCount = t), this.colCount < 1 && (this.colCount = 1);
                        var n = this.widget.config("widget.colWidth", 100),
                            o = this.widget.config("widget.colGutter", 0),
                            r = ((n + (e - this.colCount * n - o) / this.colCount) / e) * 100;
                        if (((r < 0 || r > 100) && (r = 100), (this.colWidth = r), 0 === this.columns.length))
                            for (var i = 0; i < this.colCount; i++) {
                                var s = new zo(this, r, i);
                                this.box.append(s.$el), this.columns.push(s);
                            }
                        else if (this.colCount < this.columns.length) {
                            for (i = this.columns.length - 1; i > this.colCount - 1; i--) {
                                (s = this.columns[i]).empty(), s.destroy();
                            }
                            this.columns.splice(this.colCount);
                            for (i = 0; i < this.columns.length; i++) {
                                (s = this.columns[i]).empty(), s.setWidth(r);
                            }
                            this.rerender();
                        } else if (this.colCount > this.columns.length) {
                            for (i = this.columns.length; i < this.colCount; i++) {
                                s = new zo(this, r, i);
                                this.box.append(s.$el), this.columns.push(s);
                            }
                            for (i = 0; i < this.columns.length; i++) {
                                (s = this.columns[i]).empty(), s.setWidth(r);
                            }
                            this.rerender();
                        } else console.error(this.colCount + " !+!+!! " + this.columns.length);
                    }),
                    (t.prototype.resize = function () {
                        this.log("resize");
                        var t = this.box.width();
                        if ((this.log(t + " " + this.previousWidth), t !== this.previousWidth)) {
                            var e = this.widget.config("widget.colWidth", 100),
                                n = Math.floor(t / e);
                            n < 1 && (n = 1), this.colCount !== n && ((this.visible = !0), (this.isResizing = !0), this._setCols(n), (this.isResizing = !1), (this.previousWidth = t));
                        }
                    }),
                    (t.prototype.append = function (t) {
                        this.items.push(t), this.getShortestCol().append(t);
                    }),
                    (t.prototype.rerender = function () {
                        this.log("rerender");
                        for (var t = 0; t < this.items.length; t++) this.items[t].$el.detach();
                        for (t = 0; t < this.items.length; t++) {
                            this.getShortestCol().append(this.items[t]), this.items[t].layout();
                        }
                    }),
                    (t.prototype.getShortestCol = function () {
                        for (var t = this.columns[0], e = 1; e < this.columns.length; e++) {
                            var n = this.columns[e];
                            n.colHeight() < t.colHeight() && (t = n);
                        }
                        return t;
                    }),
                    (t.prototype.empty = function () {
                        this.log("empty");
                        for (var t = 0; t < this.columns.length; t++) this.columns[t].empty();
                        this.items = [];
                    }),
                    (t.prototype.log = function (t) {
                        to.log("WaterfallLayout[" + this.id + "]->" + t);
                    }),
                    (t.prototype.destroy = function () {
                        (this.items = []), (this.columns = []), (this.isResizing = !1), (this.visible = !1), this.ro.disconnect();
                    }),
                    t
                );
            })();
        var Xo,
            qo = function (t) {
                return Ke(t, 4);
            },
            Ko = {
                share: function (t) {
                    var e = qo(t);
                    (e.url = Vo.postUrl(t)), (e.cleanText = Yn.filterHtml(t.text)), 0 !== e.url.indexOf("http") && (e.url = e.image);
                    var n = Vo.tinyparser("https://www.facebook.com/sharer/sharer.php?u={{url}}&d={{cleanText}}", e);
                    Vo.popup(n, "twitter", 600, 430, 0);
                },
            },
            Jo = {
                share: function (t) {
                    var e = qo(t);
                    (e.url = Vo.postUrl(t)), (e.cleanText = Yn.filterHtml(t.text));
                    var n = Vo.tinyparser("http://twitter.com/share?url={{url}}&text={{cleanText}}&hashtags={{hashtags}}", e);
                    Vo.popup(n, "twitter", 600, 430, 0);
                },
            },
            Yo =
                ((Xo = function (t, e) {
                    return (Xo =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(t, e);
                }),
                function (t, e) {
                    function n() {
                        this.constructor = t;
                    }
                    Xo(t, e), (t.prototype = null === e ? Object.create(e) : ((n.prototype = e.prototype), new n()));
                }),
            Qo = (function (t) {
                function e(e) {
                    var n = t.call(this) || this;
                    return (
                        (n.element = e[0]),
                        (n._videoPlaying = !1),
                        (n._alive = !0),
                        (n.onPause = n.onPause.bind(n)),
                        (n.onPlay = n.onPlay.bind(n)),
                        (n.onData = n.onData.bind(n)),
                        (n.onMetaData = n.onMetaData.bind(n)),
                        n.element.addEventListener("loadedmetadata", n.onMetaData),
                        n.element.addEventListener("loadeddata", n.onData),
                        n.element.addEventListener("play", n.onPlay),
                        n.element.addEventListener("pause", n.onPause),
                        n
                    );
                }
                return (
                    Yo(e, t),
                    (e.prototype.play = function () {
                        var t = this.element.play();
                        void 0 !== t &&
                            t.catch(function () {
                                console.error("Video failed to play");
                            });
                    }),
                    (e.prototype.pause = function () {
                        this.element.pause();
                    }),
                    (e.prototype.playPause = function () {
                        this._videoPlaying ? this.pause() : this.play();
                    }),
                    (e.prototype.isPlaying = function () {
                        return this._videoPlaying;
                    }),
                    (e.prototype.onMetaData = function () {
                        var t = this.element.videoWidth,
                            e = this.element.videoHeight;
                        t === e ? oo(this.element).addClass("aspect-square") : t > e ? oo(this.element).addClass("aspect-landscape") : oo(this.element).addClass("aspect-portrait");
                    }),
                    (e.prototype.onData = function () {}),
                    (e.prototype.onPlay = function () {
                        (this._videoPlaying = !0), this.trigger("state:changed", this._videoPlaying);
                    }),
                    (e.prototype.onPause = function () {
                        this._alive ? ((this._videoPlaying = !1), this.trigger("state:changed", this._videoPlaying)) : console.log("onPause on destroyed");
                    }),
                    (e.prototype.destroy = function () {
                        (this._alive = !1),
                            this.element.removeEventListener("loadedmetadata", this.onMetaData),
                            this.element.removeEventListener("loadeddata", this.onData),
                            this.element.removeEventListener("play", this.onPlay),
                            this.element.removeEventListener("pause", this.onPause),
                            this._videoPlaying && this.element.pause(),
                            t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(o),
            tr = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            er = (function (t) {
                function e() {
                    var e = t.call(this) || this;
                    return (e.$refs = {}), (e.templateId = ""), (e.$refs = {}), e;
                }
                return (
                    tr(e, t),
                    (e.prototype.render = function () {
                        var t = this.widget ? this.widget._config : {},
                            e = this.json ? this.json : {},
                            n = Do.renderTemplate(this.templateId, e, t);
                        this.onHandler(n, "c-on:click-prevent", "click", !0),
                            this.onHandler(n, "c-on:click", "click"),
                            this.onHandler(n, "c-on:blur", "blur"),
                            this.onHandler(n, "c-on:change", "change"),
                            this.onHandler(n, "c-on:load", "load"),
                            this.onHandler(n, "c-on:error", "error");
                        for (var o = n.find("[ref]"), r = 0; r < o.length; r++) {
                            var i = o[r],
                                s = oo(i).attr("ref");
                            this.$refs[s] = oo(i);
                        }
                        this._$el ? this._$el.replaceWith(n) : (this._$el = n);
                    }),
                    Object.defineProperty(e.prototype, "$el", {
                        get: function () {
                            if (!this._$el) throw new Error("Control.$el called before render");
                            return this._$el;
                        },
                        enumerable: !1,
                        configurable: !0,
                    }),
                    (e.prototype._fnName = function (t) {
                        return (t = (t = t.replace("(", "")).replace(")", ""));
                    }),
                    (e.prototype.onHandler = function (t, e, n, o) {
                        var r = this;
                        void 0 === o && (o = !1);
                        var i = e;
                        e = Yn.replaceAll(e, ":", "\\:");
                        var s = t.find("[" + e + "]");
                        if ("number" == typeof s.length)
                            for (
                                var a = function (t) {
                                        var e = s[t],
                                            a = c._fnName(oo(e).attr(i)),
                                            u = c,
                                            l = u[a]
                                                ? u[a]
                                                : function () {
                                                      console.error(a + "() does not exist ");
                                                  };
                                        e.addEventListener(
                                            n,
                                            function (t) {
                                                o && (t.preventDefault(), t.stopPropagation()), l.call(r, t, e);
                                            },
                                            !0
                                        );
                                    },
                                    c = this,
                                    u = 0;
                                u < s.length;
                                u++
                            )
                                a(u);
                    }),
                    (e.prototype.testInFrame = function (t) {
                        if (!this.$el) return !1;
                        var e = this.$el[0].getBoundingClientRect(),
                            n = e.y;
                        return e.y + e.height > 0 && n < t;
                    }),
                    (e.prototype.checkRefs = function (t) {
                        for (var e = 0, n = t; e < n.length; e++) {
                            var o = n[e];
                            if (!this.$refs[o]) throw new Error("Curator Widget Error: Missing ref: " + o);
                        }
                    }),
                    (e.prototype.destroy = function () {
                        this.$el && this.$el.remove(), (this.$refs = {}), delete this._$el, delete this.json, t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(o),
            nr = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            or = (function (t) {
                function e() {
                    return (null !== t && t.apply(this, arguments)) || this;
                }
                return (
                    nr(e, t),
                    (e.prototype.loadImage = function () {
                        console.log("loadImage stub");
                    }),
                    (e.prototype.fadeIn = function (t) {
                        var e = this;
                        this.$el.css({ opacity: 0 }),
                            window.setTimeout(function () {
                                e.$el.animate({ opacity: 1 });
                            }, 100 * t);
                    }),
                    (e.prototype.getHeight = function () {
                        var t = this.$el.height(),
                            e = parseInt(this.$el.css("margin-bottom")),
                            n = parseInt(this.$el.css("margin-top")),
                            o = no() ? 0 : parseInt(this.$el.css("padding-bottom"));
                        return (t += n), (t += e), (t += no() ? 0 : parseInt(this.$el.css("padding-top"))), (t += o);
                    }),
                    (e.prototype.forceHeight = function (t) {
                        this.$el && this.$el.css({ height: t });
                    }),
                    (e.prototype.layout = function () {
                        console.log("layout stub");
                    }),
                    e
                );
            })(er),
            rr = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            ir = (function (t) {
                function e(e, n) {
                    var o = t.call(this) || this;
                    if (
                        ((o.widget = e),
                        o.widget.config("forceHttps") && ((n.image = Yn.forceHttps(n.image)), (n.video = Yn.forceHttps(n.video))),
                        (o.json = n),
                        (o.json._classes = []),
                        (o.templateId = o.widget.config("post.template")),
                        !o.json.title)
                    ) {
                        var r = o.getData("title");
                        r && (o.json.title = r);
                    }
                    if (
                        (o.json.title ? o.widget.config("post.showTitles") && o.json._classes.push("crt-post-has-title") : (o.json.title = ""),
                        o.json.image ? o.json._classes.push("crt-post-has-image") : (o.json._classes.push("crt-post-content-image-hidden"), o.json._classes.push("crt-post-no-image")),
                        o.json.text ? o.json._classes.push("crt-post-has-text") : (o.json._classes.push("crt-post-content-text-hidden"), o.json._classes.push("crt-post-no-text")),
                        o.json.network_name)
                    ) {
                        var i = o.json.network_name.toLowerCase();
                        o.json._classes.push("crt-post-" + i);
                    }
                    return o.setupShopSpots(), o.widget.on(Fo.WIDGET_RESIZE, o.onWidgetResize.bind(o)), o;
                }
                return (
                    rr(e, t),
                    (e.prototype.onShareFacebookClick = function () {
                        Ko.share(this.json), this.widget.track("share:facebook");
                    }),
                    (e.prototype.onShareTwitterClick = function () {
                        Jo.share(this.json), this.widget.track("share:twitter");
                    }),
                    (e.prototype.onPostClick = function (t) {
                        if ((to.log("Post->click"), t.target)) {
                            var e = oo(t.target);
                            if (e.hasClass("read-more")) return;
                            e.is("a") && "#" !== e.attr("href") && "javascript:;" !== e.attr("href") ? this.widget.track("click:link") : (t.preventDefault(), this.trigger(Fo.POST_CLICK, t, this));
                        }
                    }),
                    (e.prototype.onReadMoreClick = function (t) {
                        this.widget.track("click:read-more"), this.trigger(Fo.POST_CLICK_READ_MORE, t, this, this.json);
                    }),
                    (e.prototype.onWidgetResize = function () {}),
                    (e.prototype.setupVideo = function () {
                        var t = this;
                        "null" !== this.json.video &&
                            this.json.video &&
                            (this.$el.addClass("crt-post-has-video"),
                            -1 === this.json.video.indexOf("youtu") &&
                                -1 === this.json.video.indexOf("vimeo") &&
                                this.widget.config("post.autoPlayVideos") &&
                                this.$refs.video &&
                                ((this.videoPlayer = new Qo(this.$refs.video)),
                                this.videoPlayer.on("state:changed", function (e, n) {
                                    t.$el.toggleClass("crt-post-video-playing", n);
                                }),
                                window.setTimeout(function () {
                                    t.videoPlayer && t.videoPlayer.play();
                                }, 200)));
                    }),
                    (e.prototype.setupCarousel = function () {
                        this.json.images && this.json.images.length > 0 && this.$el.addClass("crt-has-image-carousel");
                    }),
                    (e.prototype.setupCommentsLikes = function () {
                        this.widget.config("post.showComments") && this.$el.addClass("crt-show-comments"), this.widget.config("post.showLikes") && this.$el.addClass("crt-show-likes");
                    }),
                    (e.prototype.setupUserNameImage = function () {
                        (this.json.user_image && "https://cdn.curator.io/0.gif" !== this.json.user_image) || this.$el.addClass("crt-hide-user-image"),
                            (this.json.user_full_name && "" !== this.json.user_full_name) || this.$el.addClass("crt-hide-user-full-name"),
                            (this.json.user_screen_name && "" !== this.json.user_screen_name) || this.$el.addClass("crt-hide-user-screen-name");
                    }),
                    (e.prototype.setupShare = function () {
                        0 !== this.json.url.indexOf("http") ? this.$el.addClass("crt-post-hide-share") : this.widget.config("post.showShare") || this.$el.addClass("crt-post-hide-share");
                    }),
                    (e.prototype.setupShopSpots = function () {
                        var t = "";
                        if (this.json.spots) {
                            for (var e = 0; e < this.json.spots.length; e++) {
                                var n = this.json.spots[e].label,
                                    o = this.json.spots[e].url;
                                "" !== n && ("" !== o ? (-1 === o.indexOf("http") && (o = "http://" + o), (t += '<a href="' + o + "\" target='_blank'>" + n + "</a>, ")) : (t += n + ", "));
                            }
                            t = t.replace(/,\s*$/, "");
                        }
                        (this.json.spots_title = "In this photo: "), (this.json.spots_content = t);
                    }),
                    (e.prototype.getData = function (t) {
                        if (this.json.data && this.json.data.length > 0)
                            for (var e = 0; e < this.json.data.length; e++) {
                                var n = this.json.data[e];
                                if (n.name === t) return n.value;
                            }
                        return "";
                    }),
                    (e.prototype.destroy = function () {
                        t.prototype.destroy.call(this), this.videoPlayer && (this.videoPlayer.destroy(), (this.videoPlayer = void 0)), this.widget.off(Fo.WIDGET_RESIZE, this.onWidgetResize.bind(this));
                    }),
                    e
                );
            })(or),
            sr = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            ar = (function (t) {
                function e(e, n) {
                    var o = t.call(this, e, n) || this;
                    if (
                        ((o.json = n),
                        (o._lazyLoad = o.widget.config("widget.lazyLoad", !1)),
                        (o._progressiveLoad = o.widget.config("widget.progressiveLoad", !1)),
                        (o.json.lazyLoad = o._lazyLoad || o._progressiveLoad),
                        o.setupShopSpots(),
                        o.render(),
                        o.$refs.image.css({ opacity: 0 }),
                        (o._maxHeightSet = !1),
                        (o._maxHeightValue = 0),
                        o.$refs.image[0].addEventListener("load", o.onImageLoaded.bind(o), !0),
                        o.$refs.image[0].addEventListener("error", o.onImageError.bind(o), !0),
                        o.json.image_width > 0)
                    ) {
                        var r = (o.json.image_height / o.json.image_width) * 100;
                        o.$refs.imageContainer.addClass("crt-image-responsive").css("padding-bottom", r + "%");
                    }
                    return 0 !== o.json.url.indexOf("http") && o.$el && o.$el.find(".crt-post-share").hide(), o.setupUserNameImage(), o.setupVideo(), o.setupCarousel(), o.setupShare(), o.setupCommentsLikes(), o;
                }
                return (
                    sr(e, t),
                    (e.prototype.onImageLoaded = function () {
                        if (this.alive)
                            if (0 === this.$refs.image.attr("src").indexOf("data:image/gif;"));
                            else {
                                this.$refs.image.animate({ opacity: 1 });
                                var t = this.$refs.image[0];
                                if (t && t.naturalWidth > 0) {
                                    var e = (t.naturalHeight / t.naturalWidth) * 100;
                                    this.$refs.imageContainer.addClass("crt-image-responsive").css("padding-bottom", e + "%");
                                }
                                this.layout(), this.trigger(Fo.POST_IMAGE_LOADED, this), this.widget.trigger(Fo.POST_IMAGE_LOADED, this);
                            }
                    }),
                    (e.prototype.onImageError = function () {
                        this.alive && (this.$refs.image.hide(), this.setHeight(), this.trigger(Fo.POST_IMAGE_FAILED, this), this.widget.trigger(Fo.POST_IMAGE_FAILED, this));
                    }),
                    (e.prototype.setHeight = function () {
                        var t = this.$refs.postC.height(),
                            e = this.widget.config("post.maxHeight", 0);
                        e > 0 && t > e ? (this.$el && (this.$el.css({ maxHeight: e }), this.$el.addClass("crt-post-max-height")), (this._maxHeightSet = !0), (this._maxHeightValue = e)) : (this._maxHeightSet = !1);
                    }),
                    (e.prototype.getHeight = function () {
                        return this._maxHeightSet ? this._maxHeightValue : this.$refs.postC.height();
                    }),
                    (e.prototype.layout = function () {
                        this.setHeight(), this.layoutFooter(), this.trigger(Fo.POST_LAYOUT_CHANGED, this);
                    }),
                    (e.prototype.layoutFooter = function () {
                        if ("sydney" === this.widget.config("theme") && this.$el) {
                            var t = this.$el.find(".crt-post-footer"),
                                e = t.find(".crt-post-username"),
                                n = t.find(".crt-post-date"),
                                o = t.find(".crt-post-share"),
                                r = t.find(".crt-post-userimage"),
                                i = t.width();
                            e.width() + n.width() + o.width() + r.width() + 40 > i && e.hide();
                        }
                    }),
                    (e.prototype.loadImage = function () {
                        this.$refs.image.attr("src", this.json.image);
                    }),
                    (e.prototype.destroy = function () {
                        this.$refs.image && this.$refs.image.length && (this.$refs.image[0].removeEventListener("load", this.onImageLoaded.bind(this), !0), this.$refs.image[0].removeEventListener("error", this.onImageError.bind(this), !0)),
                            t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(ir),
            cr = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            ur = (function (t) {
                function e(e, n) {
                    var o = t.call(this) || this;
                    return (o.widget = e), (o.json = n), (o.templateId = o.widget.config("post.template")), o.widget.on(Fo.WIDGET_RESIZE, o.onWidgetResize.bind(o)), o;
                }
                return (
                    cr(e, t),
                    (e.prototype.onPostClick = function (t) {
                        if ((to.log("Post->click"), t.target)) {
                            var e = oo(t.target);
                            if (e[0] && e[0].className.indexOf("read-more") > 0) return;
                            e.is("a") && "#" !== e.attr("href") && "javascript:;" !== e.attr("href") ? this.widget.track("click:link") : (t.preventDefault(), "goto-url" === this.json.click_action && window.open(this.json.url));
                        }
                    }),
                    (e.prototype.onWidgetResize = function () {}),
                    (e.prototype.setupCommentsLikes = function () {
                        this.widget.config("post.showComments") && this.$el.addClass("crt-show-comments"), this.widget.config("post.showLikes") && this.$el.addClass("crt-show-likes");
                    }),
                    (e.prototype.setupUserNameImage = function () {
                        (this.json.user_image && "https://cdn.curator.io/0.gif" !== this.json.user_image) || this.$el.addClass("crt-hide-user-image"),
                            (this.json.user_full_name && "" !== this.json.user_full_name) || this.$el.addClass("crt-hide-user-full-name"),
                            (this.json.user_screen_name && "" !== this.json.user_screen_name) || this.$el.addClass("crt-hide-user-screen-name");
                    }),
                    (e.prototype.setupShare = function () {
                        0 !== this.json.url.indexOf("http") ? this.$el.addClass("crt-post-hide-share") : this.widget.config("post.showShare") || this.$el.addClass("crt-post-hide-share");
                    }),
                    (e.prototype.fadeIn = function (t) {
                        var e = this;
                        this.$el.css({ opacity: 0 }),
                            window.setTimeout(function () {
                                e.$el.animate({ opacity: 1 });
                            }, 100 * t);
                    }),
                    (e.prototype.destroy = function () {
                        t.prototype.destroy.call(this), this.widget.off(Fo.WIDGET_RESIZE, this.onWidgetResize.bind(this));
                    }),
                    e
                );
            })(or),
            lr = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            dr = (function (t) {
                function e(e, n) {
                    var o = t.call(this, e, n) || this;
                    (o.json = n), (o.templateId = "ad-general");
                    var r = o.widget.config("widget.lazyLoad", !1),
                        i = o.widget.config("widget.progressiveLoad", !1);
                    if (((o.json.lazyLoad = r || i), o.render(), o.json.is_html)) {
                        var s = o.json.text,
                            a = /<script[\s\S]*?src=['"]([A-Za-z0-9/:.]*)['"][\s\S]*?>[\s\S]*?<\/script>/gi.exec(s);
                        if (a) {
                            var c = a[0],
                                u = a[1];
                            s = s.replace(c, "");
                            var l = document.getElementsByTagName("script")[0];
                            if (l && l.parentNode) {
                                var d = document.createElement("script");
                                (d.src = u), l.parentNode.insertBefore(d, l);
                            }
                        }
                        o.$refs.text.html(s);
                    } else o.$refs.text.html(o.json.text);
                    if (("" === o.json.title && o.$el.addClass("crt-ad-no-title"), "" === o.json.image)) o.$el.addClass("crt-ad-no-image");
                    else {
                        if ((o.$refs.image[0].addEventListener("load", o.onImageLoaded.bind(o), !0), o.$refs.image[0].addEventListener("error", o.onImageError.bind(o), !0), o.json.image_width > 0)) {
                            var p = (o.json.image_height / o.json.image_width) * 100;
                            o.$refs.imageContainer.addClass("crt-image-responsive").css("padding-bottom", p + "%");
                        }
                        0 !== o.json.url.indexOf("http") && o.$el.find(".crt-post-share").hide();
                    }
                    return o.setupUserNameImage(), o;
                }
                return (
                    lr(e, t),
                    (e.prototype.onImageLoaded = function () {
                        if (this.alive)
                            if (0 === this.$refs.image.attr("src").indexOf("data:image/gif;"));
                            else {
                                this.$refs.image.animate({ opacity: 1 });
                                var t = this.$refs.image[0];
                                if (t && t.naturalWidth > 0) {
                                    var e = (t.naturalHeight / t.naturalWidth) * 100;
                                    this.$refs.imageContainer.addClass("crt-image-responsive").css("padding-bottom", e + "%");
                                }
                                this.setHeight(), this.trigger(Fo.POST_IMAGE_LOADED, this), this.widget.trigger(Fo.POST_IMAGE_LOADED, this);
                            }
                    }),
                    (e.prototype.onImageError = function () {
                        this.$refs.image.hide(), this.setHeight(), this.trigger(Fo.POST_IMAGE_FAILED, this), this.widget.trigger(Fo.POST_IMAGE_FAILED, this);
                    }),
                    (e.prototype.setHeight = function () {
                        var t = this.$refs.postC.height(),
                            e = this.widget.config("post.maxHeight", 0);
                        e > 0 && t > e && (this.$el.css({ maxHeight: e }), this.$el.addClass("crt-post-max-height")), this.layout();
                    }),
                    (e.prototype.getHeight = function () {
                        return this.$el.hasClass("crt-post-max-height"), this.$refs.postC.height();
                    }),
                    (e.prototype.loadImage = function () {
                        to.log("Ad->loadImage"), this.$refs.image.attr("src", this.json.image);
                    }),
                    (e.prototype.layout = function () {
                        this.layoutFooter(), this.trigger(Fo.POST_LAYOUT_CHANGED, this);
                    }),
                    (e.prototype.layoutFooter = function () {
                        var t = this.$el.find(".crt-post-username"),
                            e = this.$el.find(".crt-date"),
                            n = this.$el.find(".crt-post-footer"),
                            o = this.$el.find(".crt-post-share"),
                            r = this.$el.find(".crt-post-userimage"),
                            i = n.width();
                        t.width() + e.width() + o.width() + r.width() + 40 > i && t.hide();
                    }),
                    (e.prototype.destroy = function () {
                        this.$refs.image && this.$refs.image.length && (this.$refs.image[0].removeEventListener("load", this.onImageLoaded.bind(this), !0), this.$refs.image[0].removeEventListener("error", this.onImageError.bind(this), !0)),
                            t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(ur);
        var pr = function (t) {
            return this.__data__.set(t, "__lodash_hash_undefined__"), this;
        };
        var hr = function (t) {
            return this.__data__.has(t);
        };
        function fr(t) {
            var e = -1,
                n = null == t ? 0 : t.length;
            for (this.__data__ = new pt(); ++e < n; ) this.add(t[e]);
        }
        (fr.prototype.add = fr.prototype.push = pr), (fr.prototype.has = hr);
        var gr = fr;
        var vr = function (t, e) {
            for (var n = -1, o = null == t ? 0 : t.length; ++n < o; ) if (e(t[n], n, t)) return !0;
            return !1;
        };
        var mr = function (t, e) {
            return t.has(e);
        };
        var yr = function (t, e, n, o, r, i) {
            var s = 1 & n,
                a = t.length,
                c = e.length;
            if (a != c && !(s && c > a)) return !1;
            var u = i.get(t);
            if (u && i.get(e)) return u == e;
            var l = -1,
                d = !0,
                p = 2 & n ? new gr() : void 0;
            for (i.set(t, e), i.set(e, t); ++l < a; ) {
                var h = t[l],
                    f = e[l];
                if (o) var g = s ? o(f, h, l, e, t, i) : o(h, f, l, t, e, i);
                if (void 0 !== g) {
                    if (g) continue;
                    d = !1;
                    break;
                }
                if (p) {
                    if (
                        !vr(e, function (t, e) {
                            if (!mr(p, e) && (h === t || r(h, t, n, o, i))) return p.push(e);
                        })
                    ) {
                        d = !1;
                        break;
                    }
                } else if (h !== f && !r(h, f, n, o, i)) {
                    d = !1;
                    break;
                }
            }
            return i.delete(t), i.delete(e), d;
        };
        var wr = function (t) {
            var e = -1,
                n = Array(t.size);
            return (
                t.forEach(function (t, o) {
                    n[++e] = [o, t];
                }),
                n
            );
        };
        var _r = function (t) {
                var e = -1,
                    n = Array(t.size);
                return (
                    t.forEach(function (t) {
                        n[++e] = t;
                    }),
                    n
                );
            },
            br = _ ? _.prototype : void 0,
            Ar = br ? br.valueOf : void 0;
        var Cr = function (t, e, n, o, r, i, a) {
                switch (n) {
                    case "[object DataView]":
                        if (t.byteLength != e.byteLength || t.byteOffset != e.byteOffset) return !1;
                        (t = t.buffer), (e = e.buffer);
                    case "[object ArrayBuffer]":
                        return !(t.byteLength != e.byteLength || !i(new De(t), new De(e)));
                    case "[object Boolean]":
                    case "[object Date]":
                    case "[object Number]":
                        return s(+t, +e);
                    case "[object Error]":
                        return t.name == e.name && t.message == e.message;
                    case "[object RegExp]":
                    case "[object String]":
                        return t == e + "";
                    case "[object Map]":
                        var c = wr;
                    case "[object Set]":
                        var u = 1 & o;
                        if ((c || (c = _r), t.size != e.size && !u)) return !1;
                        var l = a.get(t);
                        if (l) return l == e;
                        (o |= 2), a.set(t, e);
                        var d = yr(c(t), c(e), o, r, i, a);
                        return a.delete(t), d;
                    case "[object Symbol]":
                        if (Ar) return Ar.call(t) == Ar.call(e);
                }
                return !1;
            },
            Pr = Object.prototype.hasOwnProperty;
        var Er = function (t, e, n, o, r, i) {
                var s = 1 & n,
                    a = ge(t),
                    c = a.length;
                if (c != ge(e).length && !s) return !1;
                for (var u = c; u--; ) {
                    var l = a[u];
                    if (!(s ? l in e : Pr.call(e, l))) return !1;
                }
                var d = i.get(t);
                if (d && i.get(e)) return d == e;
                var p = !0;
                i.set(t, e), i.set(e, t);
                for (var h = s; ++u < c; ) {
                    var f = t[(l = a[u])],
                        g = e[l];
                    if (o) var v = s ? o(g, f, l, e, t, i) : o(f, g, l, t, e, i);
                    if (!(void 0 === v ? f === g || r(f, g, n, o, i) : v)) {
                        p = !1;
                        break;
                    }
                    h || (h = "constructor" == l);
                }
                if (p && !h) {
                    var m = t.constructor,
                        y = e.constructor;
                    m == y || !("constructor" in t) || !("constructor" in e) || ("function" == typeof m && m instanceof m && "function" == typeof y && y instanceof y) || (p = !1);
                }
                return i.delete(t), i.delete(e), p;
            },
            kr = Object.prototype.hasOwnProperty;
        var Or = function (t, e, n, o, r, i) {
            var s = St(t),
                a = St(e),
                c = s ? "[object Array]" : Oe(t),
                u = a ? "[object Array]" : Oe(e),
                l = "[object Object]" == (c = "[object Arguments]" == c ? "[object Object]" : c),
                d = "[object Object]" == (u = "[object Arguments]" == u ? "[object Object]" : u),
                p = c == u;
            if (p && Object(Dt.a)(t)) {
                if (!Object(Dt.a)(e)) return !1;
                (s = !0), (l = !1);
            }
            if (p && !l) return i || (i = new gt()), s || Ht(t) ? yr(t, e, n, o, r, i) : Cr(t, e, c, n, o, r, i);
            if (!(1 & n)) {
                var h = l && kr.call(t, "__wrapped__"),
                    f = d && kr.call(e, "__wrapped__");
                if (h || f) {
                    var g = h ? t.value() : t,
                        v = f ? e.value() : e;
                    return i || (i = new gt()), r(g, v, n, o, i);
                }
            }
            return !!p && (i || (i = new gt()), Er(t, e, n, o, r, i));
        };
        var Tr = function t(e, n, o, r, i) {
            return e === n || (null == e || null == n || (!Ct(e) && !Ct(n)) ? e != e && n != n : Or(e, n, o, r, t, i));
        };
        var Sr = function (t, e, n, o) {
            var r = n.length,
                i = r,
                s = !o;
            if (null == t) return !i;
            for (t = Object(t); r--; ) {
                var a = n[r];
                if (s && a[2] ? a[1] !== t[a[0]] : !(a[0] in t)) return !1;
            }
            for (; ++r < i; ) {
                var c = (a = n[r])[0],
                    u = t[c],
                    l = a[1];
                if (s && a[2]) {
                    if (void 0 === u && !(c in t)) return !1;
                } else {
                    var d = new gt();
                    if (o) var p = o(u, l, c, t, e, d);
                    if (!(void 0 === p ? Tr(l, u, 3, o, d) : p)) return !1;
                }
            }
            return !0;
        };
        var Dr = function (t) {
            return t == t && !D(t);
        };
        var Fr = function (t) {
            for (var e = qt(t), n = e.length; n--; ) {
                var o = e[n],
                    r = t[o];
                e[n] = [o, r, Dr(r)];
            }
            return e;
        };
        var Lr = function (t, e) {
            return function (n) {
                return null != n && n[t] === e && (void 0 !== e || t in Object(n));
            };
        };
        var xr = function (t) {
                var e = Fr(t);
                return 1 == e.length && e[0][2]
                    ? Lr(e[0][0], e[0][1])
                    : function (n) {
                          return n === t || Sr(n, t, e);
                      };
            },
            Ir = /\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/,
            jr = /^\w*$/;
        var Br = function (t, e) {
            if (St(t)) return !1;
            var n = typeof t;
            return !("number" != n && "symbol" != n && "boolean" != n && null != t && !Io(t)) || jr.test(t) || !Ir.test(t) || (null != e && t in Object(e));
        };
        function $r(t, e) {
            if ("function" != typeof t || (null != e && "function" != typeof e)) throw new TypeError("Expected a function");
            var n = function () {
                var o = arguments,
                    r = e ? e.apply(this, o) : o[0],
                    i = n.cache;
                if (i.has(r)) return i.get(r);
                var s = t.apply(this, o);
                return (n.cache = i.set(r, s) || i), s;
            };
            return (n.cache = new ($r.Cache || pt)()), n;
        }
        $r.Cache = pt;
        var Nr = $r;
        var Hr = /[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g,
            Mr = /\\(\\)?/g,
            Rr = (function (t) {
                var e = Nr(t, function (t) {
                        return 500 === n.size && n.clear(), t;
                    }),
                    n = e.cache;
                return e;
            })(function (t) {
                var e = [];
                return (
                    46 === t.charCodeAt(0) && e.push(""),
                    t.replace(Hr, function (t, n, o, r) {
                        e.push(o ? r.replace(Mr, "$1") : n || t);
                    }),
                    e
                );
            });
        var Wr = function (t, e) {
                for (var n = -1, o = null == t ? 0 : t.length, r = Array(o); ++n < o; ) r[n] = e(t[n], n, t);
                return r;
            },
            Gr = _ ? _.prototype : void 0,
            zr = Gr ? Gr.toString : void 0;
        var Vr = function t(e) {
            if ("string" == typeof e) return e;
            if (St(e)) return Wr(e, t) + "";
            if (Io(e)) return zr ? zr.call(e) : "";
            var n = e + "";
            return "0" == n && 1 / e == -1 / 0 ? "-0" : n;
        };
        var Ur = function (t) {
            return null == t ? "" : Vr(t);
        };
        var Zr = function (t, e) {
            return St(t) ? t : Br(t, e) ? [t] : Rr(Ur(t));
        };
        var Xr = function (t) {
            if ("string" == typeof t || Io(t)) return t;
            var e = t + "";
            return "0" == e && 1 / t == -1 / 0 ? "-0" : e;
        };
        var qr = function (t, e) {
            for (var n = 0, o = (e = Zr(e, t)).length; null != t && n < o; ) t = t[Xr(e[n++])];
            return n && n == o ? t : void 0;
        };
        var Kr = function (t, e, n) {
            var o = null == t ? void 0 : qr(t, e);
            return void 0 === o ? n : o;
        };
        var Jr = function (t, e) {
            return null != t && e in Object(t);
        };
        var Yr = function (t, e, n) {
            for (var o = -1, r = (e = Zr(e, t)).length, i = !1; ++o < r; ) {
                var s = Xr(e[o]);
                if (!(i = null != t && n(t, s))) break;
                t = t[s];
            }
            return i || ++o != r ? i : !!(r = null == t ? 0 : t.length) && xt(r) && Lt(s, r) && (St(t) || Tt(t));
        };
        var Qr = function (t, e) {
            return null != t && Yr(t, e, Jr);
        };
        var ti = function (t, e) {
            return Br(t) && Dr(e)
                ? Lr(Xr(t), e)
                : function (n) {
                      var o = Kr(n, t);
                      return void 0 === o && o === e ? Qr(n, t) : Tr(e, o, 3);
                  };
        };
        var ei = function (t) {
            return function (e) {
                return null == e ? void 0 : e[t];
            };
        };
        var ni = function (t) {
            return function (e) {
                return qr(e, t);
            };
        };
        var oi = function (t) {
            return Br(t) ? ei(Xr(t)) : ni(t);
        };
        var ri = function (t) {
            return "function" == typeof t ? t : null == t ? Qe : "object" == typeof t ? (St(t) ? ti(t[0], t[1]) : xr(t)) : oi(t);
        };
        var ii = function (t) {
            return function (e, n, o) {
                var r = Object(e);
                if (!Xt(e)) {
                    var i = ri(n, 3);
                    (e = qt(e)),
                        (n = function (t) {
                            return i(r[t], t, r);
                        });
                }
                var s = t(e, n, o);
                return s > -1 ? r[i ? e[s] : s] : void 0;
            };
        };
        var si = function (t, e, n, o) {
            for (var r = t.length, i = n + (o ? 1 : -1); o ? i-- : ++i < r; ) if (e(t[i], i, t)) return i;
            return -1;
        };
        var ai = function (t) {
            return t ? ((t = Mo(t)) === 1 / 0 || t === -1 / 0 ? 17976931348623157e292 * (t < 0 ? -1 : 1) : t == t ? t : 0) : 0 === t ? t : 0;
        };
        var ci = function (t) {
                var e = ai(t),
                    n = e % 1;
                return e == e ? (n ? e - n : e) : 0;
            },
            ui = Math.max;
        var li = ii(function (t, e, n) {
                var o = null == t ? 0 : t.length;
                if (!o) return -1;
                var r = null == n ? 0 : ci(n);
                return r < 0 && (r = ui(o + r, 0)), si(t, ri(e, 3), r);
            }),
            di = [
                { id: 1, name: "Twitter", slug: "twitter", icon: "crt-icon-twitter" },
                { id: 2, name: "Instagram", slug: "instagram", icon: "crt-icon-instagram" },
                { id: 3, name: "Facebook", slug: "facebook", icon: "crt-icon-facebook" },
                { id: 4, name: "Pinterest", slug: "pinterest", icon: "crt-icon-pinterest" },
                { id: 5, name: "Google", slug: "google", icon: "crt-icon-google" },
                { id: 6, name: "Vine", slug: "vine", icon: "crt-icon-vine" },
                { id: 7, name: "Flickr", slug: "flickr", icon: "crt-icon-flickr" },
                { id: 8, name: "YouTube", slug: "youtube", icon: "crt-icon-youtube" },
                { id: 9, name: "Tumblr", slug: "tumblr", icon: "crt-icon-tumblr" },
                { id: 10, name: "RSS", slug: "rss", icon: "crt-icon-rss" },
                { id: 11, name: "LinkedIn", slug: "linkedin", icon: "crt-icon-linkedin" },
                { id: 12, name: "Vimeo", slug: "vimeo", icon: "crt-icon-vimeo" },
                { id: 13, name: "Diffbot", slug: "difbot", icon: "crt-icon-cog" },
                { id: 14, name: "Webo", slug: "webo", icon: "crt-icon-weibo" },
                { id: 15, name: "Glassdoor", slug: "glassdoor", icon: "crt-icon-cog" },
                { id: 16, name: "Instagram", slug: "instagram", icon: "crt-icon-instagram" },
                { id: 17, name: "Yelp", slug: "yelp", icon: "crt-icon-yelp" },
                { id: 18, name: "DeviantArt", slug: "deviant-art", icon: "crt-icon-deviantart" },
                { id: 19, name: "Behance", slug: "deviant-art", icon: "crt-icon-behance" },
                { id: 20, name: "Spotify", slug: "spotify", icon: "crt-icon-spotify" },
                { id: 21, name: "Slack", slug: "slack", icon: "crt-icon-slack" },
                { id: 22, name: "Giphy", slug: "giphy", icon: "crt-icon-giphy" },
                { id: 25, name: "Tiktok", slug: "tiktok", icon: "crt-icon-tiktok" },
            ],
            pi = {
                findById: function (t) {
                    return li(di, function (e) {
                        return e.id === t;
                    });
                },
            },
            hi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            fi = (function (t) {
                function e(e) {
                    var n = t.call(this) || this;
                    return (
                        (n.filtersLoaded = !1),
                        to.log("Filter->construct"),
                        (n.widget = e),
                        (n.templateId = n.widget.config("filter.template")),
                        n.render(),
                        n.$el.on("click", ".crt-filter-networks a", n.onNetworkClick.bind(n)),
                        n.$el.on("click", ".crt-filter-sources a", n.onSourceClick.bind(n)),
                        n.widget.on(Fo.FEED_LOADED, n.onPostsLoaded.bind(n)),
                        n
                    );
                }
                return (
                    hi(e, t),
                    (e.prototype.onPostsLoaded = function (t) {
                        console.log("Filter->onPostsLoaded");
                        var e = t.networks,
                            n = t.sources,
                            o = !1,
                            r = !1;
                        if (!this.filtersLoaded) {
                            if (
                                (this.widget.config("filter.showAll") &&
                                    (this.$refs.networksUl.append('<li class="active all"><a href="#" data-network="">All</a></li>'), this.$refs.sourcesUl.append('<li class="active all"><a href="#" data-network="">All</a></li>')),
                                this.widget.config("filter.showNetworks"))
                            )
                                for (var i = 0, s = e; i < s.length; i++) {
                                    var a = s[i];
                                    if ((d = pi.findById(a))) {
                                        var c = "";
                                        "all_" + a === this.widget.config("filter.default") &&
                                            ((c = "active"), (o = !0), this.widget.trigger(Fo.FILTER_CHANGED, this), (this.widget.feed.params.network_id = "" + a), this.$el.find(".crt-filter-networks .all").removeClass("active")),
                                            this.$refs.networksUl.append('<li class="' + c + '"><a href="#" data-network="' + a + '"><i class="' + d.icon + '"></i> ' + d.name + "</a></li>");
                                    }
                                }
                            else this.$refs.networks.hide();
                            if (this.widget.config("filter.showSources"))
                                for (var u = 0, l = n; u < l.length; u++) {
                                    var d,
                                        p = l[u];
                                    if ((d = pi.findById(p.network_id))) {
                                        c = "";
                                        p.id === this.widget.config("filter.default") &&
                                            ((c = "active"), (r = !0), this.widget.trigger(Fo.FILTER_CHANGED, this), (this.widget.feed.params.source_id = "" + p.id), this.$el.find(".crt-filter-sources .all").removeClass("active")),
                                            this.$refs.sourcesUl.append('<li class="' + c + '"><a href="#" data-source="' + p.id + '"><i class="' + d.icon + '"></i> ' + p.name + "</a></li>");
                                    }
                                }
                            else this.$refs.sources.hide();
                            (this.filtersLoaded = !0), (o || r) && this.widget.reload();
                        }
                    }),
                    (e.prototype.onSourceClick = function (t) {
                        if ((t.preventDefault(), t.target)) {
                            var e = oo(t.target),
                                n = e.data("source");
                            e.parent().hasClass("active") ? (this.$el.find(".crt-filter-sources li").removeClass("active"), (n = "0")) : (this.$el.find(".crt-filter-sources li").removeClass("active"), e.parent().addClass("active")),
                                this.widget.trigger(Fo.FILTER_CHANGED, this),
                                (this.widget.feed.params.source_id = n || ""),
                                this.widget.reload();
                        }
                    }),
                    (e.prototype.onNetworkClick = function (t) {
                        if ((t.preventDefault(), t.target)) {
                            var e = oo(t.target),
                                n = parseInt(e.data("network"));
                            e.parent().hasClass("active") ? (this.$el.find(".crt-filter-networks li").removeClass("active"), (n = 0)) : (this.$el.find(".crt-filter-networks li").removeClass("active"), e.parent().addClass("active")),
                                this.widget.trigger(Fo.FILTER_CHANGED, this),
                                (this.widget.feed.params.network_id = n ? "" + n : ""),
                                this.widget.reload();
                        }
                    }),
                    (e.prototype.destroy = function () {
                        this.$el.remove();
                    }),
                    e
                );
            })(er),
            gi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            vi = function (t, e, n, o) {
                return new (n || (n = Promise))(function (r, i) {
                    function s(t) {
                        try {
                            c(o.next(t));
                        } catch (t) {
                            i(t);
                        }
                    }
                    function a(t) {
                        try {
                            c(o.throw(t));
                        } catch (t) {
                            i(t);
                        }
                    }
                    function c(t) {
                        var e;
                        t.done
                            ? r(t.value)
                            : ((e = t.value),
                              e instanceof n
                                  ? e
                                  : new n(function (t) {
                                        t(e);
                                    })).then(s, a);
                    }
                    c((o = o.apply(t, e || [])).next());
                });
            },
            mi = function (t, e) {
                var n,
                    o,
                    r,
                    i,
                    s = {
                        label: 0,
                        sent: function () {
                            if (1 & r[0]) throw r[1];
                            return r[1];
                        },
                        trys: [],
                        ops: [],
                    };
                return (
                    (i = { next: a(0), throw: a(1), return: a(2) }),
                    "function" == typeof Symbol &&
                        (i[Symbol.iterator] = function () {
                            return this;
                        }),
                    i
                );
                function a(i) {
                    return function (a) {
                        return (function (i) {
                            if (n) throw new TypeError("Generator is already executing.");
                            for (; s; )
                                try {
                                    if (((n = 1), o && (r = 2 & i[0] ? o.return : i[0] ? o.throw || ((r = o.return) && r.call(o), 0) : o.next) && !(r = r.call(o, i[1])).done)) return r;
                                    switch (((o = 0), r && (i = [2 & i[0], r.value]), i[0])) {
                                        case 0:
                                        case 1:
                                            r = i;
                                            break;
                                        case 4:
                                            return s.label++, { value: i[1], done: !1 };
                                        case 5:
                                            s.label++, (o = i[1]), (i = [0]);
                                            continue;
                                        case 7:
                                            (i = s.ops.pop()), s.trys.pop();
                                            continue;
                                        default:
                                            if (!((r = s.trys), (r = r.length > 0 && r[r.length - 1]) || (6 !== i[0] && 2 !== i[0]))) {
                                                s = 0;
                                                continue;
                                            }
                                            if (3 === i[0] && (!r || (i[1] > r[0] && i[1] < r[3]))) {
                                                s.label = i[1];
                                                break;
                                            }
                                            if (6 === i[0] && s.label < r[1]) {
                                                (s.label = r[1]), (r = i);
                                                break;
                                            }
                                            if (r && s.label < r[2]) {
                                                (s.label = r[2]), s.ops.push(i);
                                                break;
                                            }
                                            r[2] && s.ops.pop(), s.trys.pop();
                                            continue;
                                    }
                                    i = e.call(t, s);
                                } catch (t) {
                                    (i = [6, t]), (o = 0);
                                } finally {
                                    n = r = 0;
                                }
                            if (5 & i[0]) throw i[1];
                            return { value: i[0] ? i[1] : void 0, done: !0 };
                        })([i, a]);
                    };
                }
            },
            yi = (function (t) {
                function e(e, n, o) {
                    var r = t.call(this) || this;
                    if (
                        ((r.currentImage = 0),
                        to.log("Popup->init "),
                        (r.widget = o),
                        r.widget.config("forceHttps") && ((n.image = Yn.forceHttps(n.image)), (n.video = Yn.forceHttps(n.video))),
                        (r.popupManager = e),
                        (r.json = n),
                        (r.json._classes = []),
                        !r.json.title)
                    ) {
                        var i = r.getData("title");
                        i && (r.json.title = i);
                    }
                    if (
                        (r.json.title ? r.json._classes.push("crt-popup-has-title") : (r.json.title = ""),
                        (r.templateId = r.widget.config("popup.template")),
                        r.setupShopSpots(),
                        r.render(),
                        r.json.image && r.$el.addClass("has-image"),
                        r.json.url && r.$el.addClass("crt-has-read-more"),
                        r.json.video)
                    )
                        if ((r.$el.addClass("has-video"), r.json.video && r.json.video.indexOf("youtu") >= 0)) {
                            r.$refs.video.remove();
                            var s =
                                '<div class="crt-responsive-video"><iframe id="ytplayer" src="https://www.youtube.com/embed/' + Yn.youtubeVideoId(r.json.video) + '?autoplay=0&rel=0&showinfo" frameborder="0" allowfullscreen></iframe></div>';
                            r.$el.find(".crt-video-container img").remove(), r.$el.find(".crt-video-container a").remove(), r.$el.find(".crt-video-container").append(s);
                        } else if (r.json.video && r.json.video.indexOf("vimeo") >= 0) {
                            r.$refs.video.remove();
                            var a = Yn.vimeoVideoId(r.json.video);
                            if (a) {
                                s =
                                    '<div class="crt-responsive-video"><iframe src="https://player.vimeo.com/video/' +
                                    a +
                                    '?color=ffffff&title=0&byline=0&portrait=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
                                r.$el.find(".crt-video-container img").remove(), r.$el.find(".crt-video-container a").remove(), r.$el.find(".crt-video-container").append(s);
                            }
                        } else
                            (r.videoPlayer = new Qo(r.$refs.video)),
                                r.videoPlayer.on("state:changed", function (t, e) {
                                    r.$el.toggleClass("video-playing", e);
                                }),
                                r.widget.config("popup.autoPlayVideos", !1) && r.videoPlayer.play();
                    if (r.json.images) {
                        r.$page = r.$el.find(".crt-pagination ul");
                        for (var c = 0; c < r.json.images.length; c++) r.$page.append('<li><a href="" data-page="' + c + '"></a></li>');
                        r.$page.find("a").on("click", r.onPageClick.bind(r)), (r.currentImage = 0), r.$page.find("li:nth-child(" + (r.currentImage + 1) + ")").addClass("selected");
                    }
                    return r.onResize(), r.createHandlers(), r;
                }
                return (
                    gi(e, t),
                    (e.prototype.setupShopSpots = function () {
                        var t = "";
                        if (this.json.spots) {
                            for (var e = 0; e < this.json.spots.length; e++) {
                                var n = this.json.spots[e].label,
                                    o = this.json.spots[e].url;
                                "" !== n && ("" !== o ? (-1 === o.indexOf("http") && (o = "http://" + o), (t += '<a href="' + o + "\" target='_blank'>" + n + "</a>, ")) : (t += n + ", "));
                            }
                            t = t.replace(/,\s*$/, "");
                        }
                        (this.json.spots_title = "In this photo: "), (this.json.spots_content = t);
                    }),
                    (e.prototype.createHandlers = function () {
                        var t = this;
                        (this.onResize = Go(this.onResize.bind(this), 100)),
                            (this.ro = new Lo.a(function (e) {
                                e.length > 0 && t.onResize();
                            })),
                            this.ro.observe(oo("body")[0]);
                    }),
                    (e.prototype.onResize = function () {
                        if ((to.log("Popup->onResize"), this.alive)) {
                            var t = oo(window).width(),
                                e = t - 80;
                            t > 1055 ? (e = 935) : t > 910 ? (e = t - 120) : t > 680 && (e = 600), this.$refs.left.css("width", e);
                        }
                    }),
                    (e.prototype.onPageClick = function (t) {
                        if ((t.preventDefault(), t.target)) {
                            var e = oo(t.target),
                                n = parseInt(e.data("page")),
                                o = this.json.images[n];
                            this.$el.find(".crt-image img").attr("src", o.url),
                                (this.currentImage = n),
                                this.$page && (this.$page.find("li").removeClass("selected"), this.$page.find("li:nth-child(" + (this.currentImage + 1) + ")").addClass("selected"));
                        }
                    }),
                    (e.prototype.onShareFacebookClick = function () {
                        return Ko.share(this.json), this.widget.track("share:facebook"), !1;
                    }),
                    (e.prototype.onShareTwitterClick = function () {
                        return Jo.share(this.json), this.widget.track("share:twitter"), !1;
                    }),
                    (e.prototype.onClose = function () {
                        return vi(this, void 0, void 0, function () {
                            return mi(this, function (t) {
                                return this.popupManager.onClose(), [2];
                            });
                        });
                    }),
                    (e.prototype.onPrevious = function () {
                        this.popupManager.onPrevious();
                    }),
                    (e.prototype.onNext = function () {
                        this.popupManager.onNext();
                    }),
                    (e.prototype.onPlay = function () {
                        to.log("Popup->onPlay"), this.videoPlayer && this.videoPlayer.playPause();
                    }),
                    (e.prototype.getData = function (t) {
                        if (this.json.data && this.json.data.length > 0)
                            for (var e = 0; e < this.json.data.length; e++) {
                                var n = this.json.data[e];
                                if (n.name === t) return n.value;
                            }
                    }),
                    (e.prototype.show = function () {
                        var t = this;
                        return new Promise(function (e) {
                            t.$el.fadeIn(function () {
                                e(!0);
                            });
                        });
                    }),
                    (e.prototype.hide = function () {
                        var t = this;
                        return (
                            to.log("Popup->hide"),
                            this.videoPlayer && this.videoPlayer.pause(),
                            new Promise(function (e) {
                                t.$el.fadeOut(function () {
                                    e(!0);
                                });
                            })
                        );
                    }),
                    (e.prototype.hideAndDestroy = function () {
                        return vi(this, void 0, void 0, function () {
                            return mi(this, function (t) {
                                switch (t.label) {
                                    case 0:
                                        return [4, this.hide()];
                                    case 1:
                                        return t.sent(), this.destroy(), [2, !0];
                                }
                            });
                        });
                    }),
                    (e.prototype.destroy = function () {
                        to.log("Popup->destroy"), this.ro && this.ro.disconnect(), this.videoPlayer && this.videoPlayer.destroy(), delete this.videoPlayer, t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(er),
            wi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            _i = function (t, e, n, o) {
                return new (n || (n = Promise))(function (r, i) {
                    function s(t) {
                        try {
                            c(o.next(t));
                        } catch (t) {
                            i(t);
                        }
                    }
                    function a(t) {
                        try {
                            c(o.throw(t));
                        } catch (t) {
                            i(t);
                        }
                    }
                    function c(t) {
                        var e;
                        t.done
                            ? r(t.value)
                            : ((e = t.value),
                              e instanceof n
                                  ? e
                                  : new n(function (t) {
                                        t(e);
                                    })).then(s, a);
                    }
                    c((o = o.apply(t, e || [])).next());
                });
            },
            bi = function (t, e) {
                var n,
                    o,
                    r,
                    i,
                    s = {
                        label: 0,
                        sent: function () {
                            if (1 & r[0]) throw r[1];
                            return r[1];
                        },
                        trys: [],
                        ops: [],
                    };
                return (
                    (i = { next: a(0), throw: a(1), return: a(2) }),
                    "function" == typeof Symbol &&
                        (i[Symbol.iterator] = function () {
                            return this;
                        }),
                    i
                );
                function a(i) {
                    return function (a) {
                        return (function (i) {
                            if (n) throw new TypeError("Generator is already executing.");
                            for (; s; )
                                try {
                                    if (((n = 1), o && (r = 2 & i[0] ? o.return : i[0] ? o.throw || ((r = o.return) && r.call(o), 0) : o.next) && !(r = r.call(o, i[1])).done)) return r;
                                    switch (((o = 0), r && (i = [2 & i[0], r.value]), i[0])) {
                                        case 0:
                                        case 1:
                                            r = i;
                                            break;
                                        case 4:
                                            return s.label++, { value: i[1], done: !1 };
                                        case 5:
                                            s.label++, (o = i[1]), (i = [0]);
                                            continue;
                                        case 7:
                                            (i = s.ops.pop()), s.trys.pop();
                                            continue;
                                        default:
                                            if (!((r = s.trys), (r = r.length > 0 && r[r.length - 1]) || (6 !== i[0] && 2 !== i[0]))) {
                                                s = 0;
                                                continue;
                                            }
                                            if (3 === i[0] && (!r || (i[1] > r[0] && i[1] < r[3]))) {
                                                s.label = i[1];
                                                break;
                                            }
                                            if (6 === i[0] && s.label < r[1]) {
                                                (s.label = r[1]), (r = i);
                                                break;
                                            }
                                            if (r && s.label < r[2]) {
                                                (s.label = r[2]), s.ops.push(i);
                                                break;
                                            }
                                            r[2] && s.ops.pop(), s.trys.pop();
                                            continue;
                                    }
                                    i = e.call(t, s);
                                } catch (t) {
                                    (i = [6, t]), (o = 0);
                                } finally {
                                    n = r = 0;
                                }
                            if (5 & i[0]) throw i[1];
                            return { value: i[0] ? i[1] : void 0, done: !0 };
                        })([i, a]);
                    };
                }
            },
            Ai = (function (t) {
                function e(e, n) {
                    var o = t.call(this) || this;
                    return (o._hiding = !1), (o._showing = !1), to.log("PopupManager->init "), (o.widget = e), (o.feed = n), (o.currentPostNum = 0), (o.templateId = o.widget.config("popup.templateWrapper")), o.render(), o;
                }
                return (
                    wi(e, t),
                    (e.prototype.render = function () {
                        t.prototype.render.call(this), oo("body").append(this.$el);
                    }),
                    (e.prototype.showPopup = function (t) {
                        return _i(this, void 0, void 0, function () {
                            var e;
                            return bi(this, function (n) {
                                switch (n.label) {
                                    case 0:
                                        return to.log("PopupManager->showPopup " + t.id), this._showing ? [2] : ((this._showing = !0), this.popup ? [4, this.popup.hideAndDestroy()] : [3, 2]);
                                    case 1:
                                        n.sent(), (n.label = 2);
                                    case 2:
                                        return (
                                            (this.popup = new yi(this, t, this.widget)),
                                            this.$refs.container.append(this.popup.$el),
                                            this.$el.show(),
                                            "block" !== this.$refs.underlay.css("display") && this.$refs.underlay.fadeIn(),
                                            [4, this.popup.show()]
                                        );
                                    case 3:
                                        if ((n.sent(), oo("body").addClass("crt-popup-visible"), (this.currentPostNum = 0), this.feed._posts))
                                            for (e = 0; e < this.feed._posts.length; e++)
                                                if (t.id === this.feed._posts[e].id) {
                                                    (this.currentPostNum = e), to.log("Found post " + e);
                                                    break;
                                                }
                                        return this.addDeepLink(t), this.widget.track("popup:show"), (this._showing = !1), [2];
                                }
                            });
                        });
                    }),
                    (e.prototype.onClose = function () {
                        this.hide();
                    }),
                    (e.prototype.onPrevious = function () {
                        (this.currentPostNum -= 1), (this.currentPostNum = this.currentPostNum >= 0 ? this.currentPostNum : this.feed._posts.length - 1), this.showPopup(this.feed._posts[this.currentPostNum]);
                    }),
                    (e.prototype.onNext = function () {
                        (this.currentPostNum += 1), (this.currentPostNum = this.currentPostNum < this.feed._posts.length ? this.currentPostNum : 0), this.showPopup(this.feed._posts[this.currentPostNum]);
                    }),
                    (e.prototype.onUnderlayClick = function () {
                        return _i(this, void 0, void 0, function () {
                            return bi(this, function (t) {
                                switch (t.label) {
                                    case 0:
                                        return to.log("PopupManager->onUnderlayClick"), [4, this.hide()];
                                    case 1:
                                        return t.sent(), [2, !0];
                                }
                            });
                        });
                    }),
                    (e.prototype.hide = function () {
                        return _i(this, void 0, void 0, function () {
                            var t = this;
                            return bi(this, function (e) {
                                switch (e.label) {
                                    case 0:
                                        return (
                                            to.log("PopupManager->hide"),
                                            this._hiding
                                                ? (to.log(" ... dbl click? ignoring"), [2])
                                                : ((this._hiding = !0), this.widget.track("popup:hide"), oo("body").removeClass("crt-popup-visible"), (this.currentPostNum = 0), this.popup ? [4, this.popup.hideAndDestroy()] : [3, 2])
                                        );
                                    case 1:
                                        e.sent(), (this.popup = void 0), (e.label = 2);
                                    case 2:
                                        return (
                                            this.$refs.underlay &&
                                                this.$refs.underlay.fadeOut(function () {
                                                    t.$refs.underlay && (t.$refs.underlay.css({ display: "", opacity: "" }), t.$el.hide());
                                                }),
                                            this.removeDeepLink(),
                                            (this._hiding = !1),
                                            [2]
                                        );
                                }
                            });
                        });
                    }),
                    (e.prototype.removeDeepLink = function () {
                        if (this.widget.config("popup.deepLink")) {
                            var t = window.document.location.hash;
                            if ((t = t.replace("#", "")).indexOf("crt:post:") > -1) {
                                var e = window.location.origin + window.location.pathname;
                                window.history && window.history.replaceState(null, "", e);
                            }
                        }
                    }),
                    (e.prototype.addDeepLink = function (t) {
                        this.widget.config("popup.deepLink") && (window.history ? window.history.pushState(null, "", "#crt:post:" + t.id) : (window.document.location.hash = "crt:post:" + t.id));
                    }),
                    (e.prototype.destroy = function () {
                        this.popup && (this.popup.destroy(), delete this.popup), this.$el.remove(), t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(er),
            Ci = ["responseType", "withCredentials", "timeout", "onprogress"];
        function Pi(t, e, n) {
            t[e] = t[e] || n;
        }
        var Ei = function (t, e) {
            var n = t.headers || {},
                o = t.body,
                r = t.method || (o ? "POST" : "GET"),
                i = !1,
                s = (function (t) {
                    if (t && window.XDomainRequest && !/MSIE 1/.test(window.navigator.userAgent)) return new window.XDomainRequest();
                    if (window.XMLHttpRequest) return new window.XMLHttpRequest();
                })(t.cors);
            function a(t, n) {
                return function () {
                    i || (e(void 0 === s.status ? t : s.status, 0 === s.status ? "Error" : s.response || s.responseText || n, s), (i = !0));
                };
            }
            s.open(r, t.url, !0);
            var c = (s.onload = a(200));
            (s.onreadystatechange = function () {
                4 === s.readyState && c();
            }),
                (s.onerror = a(null, "Error")),
                (s.ontimeout = a(null, "Timeout")),
                (s.onabort = a(null, "Abort")),
                o && (Pi(n, "X-Requested-With", "XMLHttpRequest"), (window.FormData && o instanceof window.FormData) || Pi(n, "Content-Type", "application/x-www-form-urlencoded"));
            for (var u = 0, l = Ci.length; u < l; u++) void 0 !== t[(d = Ci[u])] && (s[d] = t[d]);
            for (var d in n) s.setRequestHeader(d, n[d]);
            return s.send(o), s;
        };
        function ki(t) {
            for (var e = Object.keys(t), n = [], o = 0; o < e.length; o++) {
                var r = e[o];
                n.push(r + "=" + encodeURIComponent(t[r]));
            }
            return "?" + n.join("&");
        }
        function Oi(t) {
            var e = window.location.protocol,
                n = t.indexOf("://");
            return n && (t = t.substr(n + 3)), (t = (e = "https:" !== e && "http:" !== e ? "https:" : e) + "//" + t);
        }
        var Ti = {
                get: function (t, e, n, o) {
                    return (
                        (t = Oi(t)),
                        e && (t += ki(e)),
                        Ei({ url: t, cors: !0 }, function (t, e) {
                            t ? n(JSON.parse(e), t) : o(t, e);
                        })
                    );
                },
                post: function (t, e, n, o) {
                    return (
                        (t = Oi(t)),
                        Ei({ url: t, cors: !0, body: e, method: "POST" }, function (t, e) {
                            t ? n(JSON.parse(e), t) : o(t, e);
                        })
                    );
                },
                getPromise: function (t, e, n) {
                    return (
                        void 0 === n && (n = {}),
                        new Promise(function (o) {
                            n.dontChangeProtocol || (t = Oi(t)),
                                e && (t += ki(e)),
                                Ei({ url: t, cors: !0 }, function (t, e) {
                                    if (t) {
                                        var n = JSON.parse(e);
                                        o({ success: !0, data: n, text: "", statusCode: t });
                                    } else o({ success: !1, data: {}, text: e, statusCode: t });
                                });
                        })
                    );
                },
            },
            Si = (function () {
                function t(t) {
                    var e = /(.+?):\/\/([0-9A-Za-z.-]*)/.exec(t);
                    if (!e) throw Error("UriBuilder - is invalid format");
                    this.apiEndpoint = e[1] + "://" + e[2];
                }
                return (
                    (t.prototype.build = function (t, e) {
                        var n = Vo.tinyparser(t, e);
                        return this.apiEndpoint + n;
                    }),
                    t
                );
            })(),
            Di = (function () {
                function t() {}
                return (
                    (t.prototype.each = function (t) {
                        return (this.eachCb = t), this;
                    }),
                    (t.prototype.done = function (t) {
                        return (this.doneCb = t), this;
                    }),
                    (t.prototype.catch = function (t) {
                        return (this.catchCb = t), this;
                    }),
                    (t.prototype.triggerEach = function (t, e) {
                        void 0 === e && (e = 0), "function" == typeof this.eachCb && this.eachCb(t, e);
                    }),
                    (t.prototype.triggerDone = function () {
                        "function" == typeof this.doneCb && this.doneCb();
                    }),
                    (t.prototype.triggerCatch = function (t) {
                        "function" == typeof this.catchCb && this.catchCb(t);
                    }),
                    t
                );
            })(),
            Fi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            Li = function () {
                return (Li =
                    Object.assign ||
                    function (t) {
                        for (var e, n = 1, o = arguments.length; n < o; n++) for (var r in (e = arguments[n])) Object.prototype.hasOwnProperty.call(e, r) && (t[r] = e[r]);
                        return t;
                    }).apply(this, arguments);
            },
            xi = (function (t) {
                function e(e) {
                    var n = t.call(this) || this;
                    (n.networks = []),
                        (n.currentPage = 0),
                        to.log("FeedCursorAds->init with options"),
                        (n.widget = e),
                        (n.postsLoaded = 0),
                        (n.postCount = 0),
                        (n.loading = !1),
                        (n.allPostsLoaded = !1),
                        (n.pagination = { after: "", before: "" }),
                        (n._firstLoad = !0),
                        (n._feedCurrentIdx = 0),
                        (n._feed = []),
                        (n._posts = []),
                        (n._ads = []),
                        (n._active = !0),
                        (n._showAds = n.widget.config("feed.showAds", !1)),
                        (n.params = n.widget.config("feed.params") || {}),
                        (n.params.limit = n.widget.config("feed.limit", 25)),
                        (n.params.hasPoweredBy = n.widget.hasPoweredBy),
                        (n.params.version = "4.0"),
                        n.widget.config("filter.limitPosts", !1) &&
                            ((n.params.limitPosts = !0), (n.params.limitPostNumber = n.widget.config("filter.limitPostNumber", 12)), (n.params.limitPostPeriod = n.widget.config("filter.period", "hours")));
                    var o = new Si(n.widget.config("feed.apiEndpoint"));
                    return (n.urlFeedPosts = o.build("/restricted/feeds/{{feedId}}/posts", { feedId: n.widget.config("feed.id") })), (n.urlFeedPost = o.build("/restricted/feeds/{{feedId}}/posts", { feedId: n.widget.config("feed.id") })), n;
                }
                return (
                    Fi(e, t),
                    (e.prototype.reset = function () {
                        (this._feedCurrentIdx = 0), (this._feed = []), (this._posts = []), (this._ads = []), (this._firstLoad = !0), (this.allPostsLoaded = !1);
                    }),
                    (e.prototype.afterIterator = function (t) {
                        var e = this,
                            n = new Di(),
                            o = function () {
                                for (var o = e.nextPost(), r = 0; o && r < t; ) n.triggerEach(o, r), r++, (o = e.nextPost());
                                n.triggerDone();
                            };
                        return (
                            this.numPostsAfterCurrent() < t
                                ? this.load()
                                      .then(function () {
                                          o();
                                      })
                                      .catch(function (t) {
                                          n.triggerCatch(t);
                                      })
                                : window.setTimeout(function () {
                                      o();
                                  }, 10),
                            n
                        );
                    }),
                    (e.prototype.afterLoop = function (t) {
                        var e = this;
                        return new Promise(function (n, o) {
                            e.numPostsAfterCurrent() < t
                                ? e
                                      .load()
                                      .then(function () {
                                          n(e.getXPosts(t));
                                      })
                                      .catch(function (t) {
                                          o(t);
                                      })
                                : n(e.getXPosts(t));
                        });
                    }),
                    (e.prototype.loadXPosts = function (t) {
                        var e = this;
                        return (
                            to.log("FeedCursorAds->loadXPosts " + t),
                            new Promise(function (n, o) {
                                e.numPostsAfterCurrent() < t
                                    ? e.allPostsLoaded
                                        ? n(e.getXPosts(t))
                                        : e
                                              .load()
                                              .then(function () {
                                                  n(e.getXPosts(t));
                                              })
                                              .catch(function (t) {
                                                  o(t);
                                              })
                                    : n(e.getXPosts(t));
                            })
                        );
                    }),
                    (e.prototype.nextPost = function () {
                        if (this._feedCurrentIdx < this._feed.length) {
                            var t = this._feed[this._feedCurrentIdx];
                            return this._feedCurrentIdx++, t;
                        }
                    }),
                    (e.prototype.getXPosts = function (t) {
                        var e = this._feedCurrentIdx + t;
                        e > this._feed.length && (e = this._feed.length);
                        var n = this._feed.slice(this._feedCurrentIdx, e);
                        return (this._feedCurrentIdx = e), n;
                    }),
                    (e.prototype.postAtIndex = function (t) {
                        return this._feed[t];
                    }),
                    (e.prototype.numPosts = function () {
                        return this._feed.length;
                    }),
                    (e.prototype.numPostsAfterCurrent = function () {
                        return this._feed.length - this._feedCurrentIdx;
                    }),
                    (e.prototype.hasMorePosts = function () {
                        return !this.allPostsLoaded;
                    }),
                    (e.prototype.isFirstLoad = function () {
                        return this._firstLoad;
                    }),
                    (e.prototype.load = function (t) {
                        return (
                            to.log("FeedCursorAds->load " + this.loading),
                            this.loading
                                ? new Promise(function (t) {
                                      t(!1);
                                  })
                                : this._firstLoad
                                ? this.loadFirst(t)
                                : this.loadAfter(t)
                        );
                    }),
                    (e.prototype.loadFirst = function (t) {
                        if ((to.log("FeedCursorAds->loadFirst " + this.loading), this.loading))
                            return new Promise(function (t) {
                                t(!1);
                            });
                        (this._feed = []), (this.postsLoaded = 0);
                        var e = Li(Li({}, this.params), t);
                        return (e.limit = this.widget.config("feed.limit", 25)), this._loadPosts(e, "first-load");
                    }),
                    (e.prototype.loadAfter = function (t) {
                        if ((to.log("FeedCursorAds->loadAfter " + this.loading), this.loading))
                            return new Promise(function (t) {
                                t(!1);
                            });
                        var e = Li(Li({}, this.params), t);
                        return this.pagination && this.pagination.after && ((e.after = this.pagination.after), (e.before = "")), this._loadPosts(e, "after");
                    }),
                    (e.prototype.loadMore = function () {
                        return this.loadAfter();
                    }),
                    (e.prototype.loadOld = function () {
                        return this.loadAfter();
                    }),
                    (e.prototype.loadBefore = function () {
                        if ((to.log("FeedCursorAds->loadBefore " + this.loading), this.loading))
                            return new Promise(function (t) {
                                t(!1);
                            });
                        var t = Li({}, this.params);
                        return this.pagination && this.pagination.before && ((t.before = this.pagination.before), (t.after = "")), this._loadPosts(t, "before");
                    }),
                    (e.prototype.loadNew = function () {
                        return this.loadBefore();
                    }),
                    (e.prototype.loadPage = function (t) {
                        if ((to.log("FeedCursorAds->loadPage " + t), this.loading))
                            return new Promise(function (t) {
                                t(!1);
                            });
                        var e = Li({}, this.params),
                            n = e.limit || 25;
                        return (e.offset = t * n), (e.before = ""), (e.after = ""), (this.currentPage = t), this._loadPosts(e, "load-page");
                    }),
                    (e.prototype._loadPosts = function (t, e) {
                        var n = this;
                        return (
                            to.log("FeedCursorAds->_loadPosts position:" + e),
                            (this.loading = !0),
                            new Promise(function (o, r) {
                                Ti.getPromise(n.urlFeedPosts, t, { dontChangeProtocol: !0 }).then(function (t) {
                                    var i = t.success,
                                        s = t.data;
                                    if ((to.log("FeedCursorAds->_loadPosts success"), n._active))
                                        if (((n.loading = !1), (n._firstLoad = !1), i)) {
                                            if (((n.postCount = s.postCount), (n.postsLoaded += s.posts.length), (n.allPostsLoaded = n.postsLoaded >= n.postCount), (n._ads = s.ads), (n.networks = s.networks), "before" === e)) {
                                                if (s.posts.length) {
                                                    var a = [];
                                                    (a = a.concat(s.posts)), (n._feed = a.concat(n._feed));
                                                }
                                                s.pagination && s.pagination.before && (n.pagination.before = s.pagination.before);
                                            } else
                                                "after" === e
                                                    ? (n.addPostsAndAdsToEnd(s.posts), s.pagination && s.pagination.after && (n.pagination.after = s.pagination.after))
                                                    : "first-load" === e && (n.addPostsAndAdsToEnd(s.posts), s.pagination && ((n.pagination.after = s.pagination.after), (n.pagination.before = s.pagination.before)));
                                            n.widget.trigger(Fo.FEED_LOADED, s, e), n.trigger(Fo.FEED_LOADED, s, e), n.widget.trigger(Fo.POSTS_LOADED, s.posts, e), n.trigger(Fo.POSTS_LOADED, s.posts, e), o(s);
                                        } else n.trigger(Fo.POSTS_FAILED, s, e), n.widget.trigger(Fo.POSTS_FAILED, s, e), r();
                                });
                            })
                        );
                    }),
                    (e.prototype.addPostsAndAdsToEnd = function (t) {
                        if ((to.log("FeedCursorAds->addPostsAndAdsToEnd"), (this._posts = this._posts.concat(t)), this._showAds && this._ads.length))
                            if (t.length)
                                for (var e = 0; e < t.length; e++) {
                                    for (var n = t[e], o = this._feed.length + 1, r = 0; r < this._ads.length; r++) {
                                        ((i = Je(this._ads[r])).type = "ad"), (i.position_start === o || (i.position_repeats && o % i.position_repeat_interval === i.position_start)) && this._feed.push(i);
                                    }
                                    (n.type = "post"), this._feed.push(n);
                                }
                            else
                                for (r = 0; r < this._ads.length; r++) {
                                    var i;
                                    ((i = this._ads[r]).type = "ad"), this._feed.push(i);
                                }
                        else this._feed = this._feed.concat(t);
                    }),
                    (e.prototype.loadPost = function (t) {
                        to.log("FeedCursorAds->loadPost id:" + t);
                        var e = this.urlFeedPost + "/" + t;
                        return new Promise(function (t, n) {
                            Ti.getPromise(e, {}, { dontChangeProtocol: !0 }).then(function (e) {
                                var o = e.success,
                                    r = e.data;
                                to.log("FeedCursorAds->loadPost returned "), o ? t(r) : n();
                            });
                        });
                    }),
                    (e.prototype.destroy = function () {
                        to.log("FeedCursorAds->destroy"), (this._active = !1), t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(o),
            Ii = (function () {
                function t(t) {
                    (this.widget = t),
                        (this.uriBuilder = new Si(t.config("feed.apiEndpoint"))),
                        (this.urlFeedTack = this.uriBuilder.build("/restricted/feeds/{{feedId}}/track", { feedId: t.config("feed.id") })),
                        (this.urlFeedTack = this.uriBuilder.build("/restricted/feeds/{{feedId}}/track", { feedId: t.config("feed.id") }));
                }
                return (
                    (t.prototype.track = function (t) {
                        Ti.get(
                            this.urlFeedTack,
                            { a: t },
                            function () {},
                            function (t, e) {
                                to.log("Tracker->track fail"), to.log(t), to.log(e);
                            }
                        );
                    }),
                    (t.prototype.trackPostAction = function (t, e) {
                        var n = { postId: t, feedId: this.widget.config("feed.id") },
                            o = this.uriBuilder.build("/restricted/feeds/{{feedId}}/posts/{{postId}}/action", n);
                        Ti.get(
                            o,
                            { action: e },
                            function () {},
                            function (t, e) {
                                to.log("Tracker->track fail code: " + t), to.log(e);
                            }
                        );
                    }),
                    t
                );
            })();
        var ji = {
            getQuery: function (t, e) {
                void 0 === e && (e = "");
                var n = (function (t) {
                    if (0 === t.length) return {};
                    for (var e = {}, n = 0; n < t.length; ++n) {
                        var o = t[n].split("=", 2);
                        e[o[0]] = 1 === o.length ? "" : decodeURIComponent(o[1].replace(/\+/g, " "));
                    }
                    return e;
                })(window.location.search.substr(1).split("&"));
                return n[t] ? n[t] : e;
            },
        };
        var Bi = function (t, e, n, o) {
            if (!D(t)) return t;
            for (var r = -1, i = (e = Zr(e, t)).length, s = i - 1, a = t; null != a && ++r < i; ) {
                var c = Xr(e[r]),
                    u = n;
                if (r != s) {
                    var l = a[c];
                    void 0 === (u = o ? o(l, c, a) : void 0) && (u = D(l) ? l : Lt(e[r + 1]) ? [] : {});
                }
                _t(a, c, u), (a = a[c]);
            }
            return t;
        };
        var $i = function (t, e, n) {
                return null == t ? t : Bi(t, e, n);
            },
            Ni = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            Hi = (function (t) {
                function e(e, n) {
                    var o = t.call(this) || this;
                    return (
                        (o.hasPoweredBy = !1),
                        to.log("BaseWidget->construct"),
                        (o.id = Vo.uId()),
                        (o._config = {}),
                        (o._responsiveConfig = {}),
                        (o.autoLoadTimeout = 0),
                        (o.autoLoading = !1),
                        o.setOptions(e, n),
                        (o.$container = o.createContainer()),
                        o.checkPoweredBy(),
                        (o.feed = o.createFeed()),
                        (o.popupManager = o.createPopupManager()),
                        (o.tracker = o.createTracker()),
                        o.createFilter(),
                        o.checkDeepLink(),
                        o
                    );
                }
                return (
                    Ni(e, t),
                    (e.prototype.setOptions = function (t, e) {
                        if (!t) throw new Error("Widget Options missing");
                        if (("true" === ji.getQuery("curatorDebug", "false") && (t.debug = !0), (this._config = kn(t, e)), !this.config("container"))) throw new Error("Widget options.container missing");
                        if (!this.config("feed.id")) throw new Error("Widget options.feedId missing");
                    }),
                    (e.prototype.createContainer = function () {
                        var t = this.config("container", "#crt-container");
                        if (!io.checkContainer(t)) throw new Error("Widget init failed - could not find container");
                        var e = oo(t);
                        if (!e) throw new Error("Widget init failed - could not find container");
                        this.configLoadInline(e),
                            (to.debug = !!this.config("debug")),
                            to.log("Setting debug to: " + this.config("debug")),
                            Ao.setLang(this.config("lang", "en")),
                            to.log("Setting language to: " + this.config("lang")),
                            e.addClass("crt-widget"),
                            io.isTouch() ? e.addClass("crt-touch") : e.addClass("crt-no-touch");
                        var n = this.config("theme", "");
                        n && e.addClass("crt-widget-theme-" + n);
                        var o = { name: "crt:widget:created", data: { feedId: this.config("feed.id") } };
                        return window.postMessage(o, "*"), e;
                    }),
                    (e.prototype.renderWidget = function () {
                        return (this.templateId = this.config("widget.template")), this.render(), this.$el;
                    }),
                    (e.prototype.setStyles = function (t) {
                        this.createStleSheet(), console.log("BaseWidget->setStyles - this should be overridden");
                    }),
                    (e.prototype.createStleSheet = function () {
                        this.sheet ? this.clearStyles() : (this.sheet = io.createSheet());
                    }),
                    (e.prototype.clearStyles = function () {
                        this.sheet && io.deleteCSSRules(this.sheet);
                    }),
                    (e.prototype.addStyle = function (t, e) {
                        if (t) {
                            var n = [];
                            for (var o in t)
                                if (Object.prototype.hasOwnProperty.call(t, o)) {
                                    var r = Yn.camelToDash(o) + ": " + t[o];
                                    n.push(r);
                                }
                            n.length > 0 && io.addCSSRule(this.sheet, e, n.join(";"));
                        }
                    }),
                    (e.prototype.updateResponsiveOptions = function () {
                        this._responsiveConfig = this._config;
                    }),
                    (e.prototype.configLoadInline = function (t) {
                        for (var e = 0, n = ["lang", "debug"]; e < n.length; e++) {
                            var o = n[e],
                                r = t.data("crt-" + o);
                            r && $i(this._config, o, r);
                        }
                        this.updateResponsiveOptions();
                    }),
                    (e.prototype.config = function (t, e) {
                        return this._responsiveConfig || this.updateResponsiveOptions(), Kr(this._config, t, e);
                    }),
                    (e.prototype.createFeed = function () {
                        var t = new xi(this);
                        return t.on(Fo.POSTS_LOADED, this.onPostsLoaded.bind(this)), t.on(Fo.POSTS_FAILED, this.onPostsFail.bind(this)), t.on(Fo.FEED_LOADED, this.onFeedLoaded.bind(this)), t;
                    }),
                    (e.prototype.createPopupManager = function () {
                        return new Ai(this, this.feed);
                    }),
                    (e.prototype.createTracker = function () {
                        return new Ii(this);
                    }),
                    (e.prototype.createFilter = function () {
                        if ((to.log("BaseWidget->createFilter"), this.config("filter.showNetworks") || this.config("filter.showSources"))) return (this.filter = new fi(this)), this.$container.append(this.filter.$el), this.filter;
                    }),
                    (e.prototype.createPostElements = function (t) {
                        var e = this,
                            n = [];
                        return (
                            t.forEach(function (t) {
                                var o = e.createElement(t);
                                n.push(o.$el);
                            }),
                            n
                        );
                    }),
                    (e.prototype.createElement = function (t) {
                        return "ad" === t.type ? this.createAdElement(t) : this.createPostElement(t);
                    }),
                    (e.prototype.createPostElement = function (t) {
                        var e = new ar(this, t);
                        return (
                            e.on(Fo.POST_CLICK, this.onPostClick.bind(this)),
                            e.on(Fo.POST_CLICK_READ_MORE, this.onPostClickReadMore.bind(this)),
                            e.on(Fo.POST_IMAGE_LOADED, this.onPostImageLoaded.bind(this)),
                            e.on(Fo.POST_IMAGE_FAILED, this.onPostImageFailed.bind(this)),
                            this.trigger(Fo.POST_CREATED, e),
                            e
                        );
                    }),
                    (e.prototype.createAdElement = function (t) {
                        var e = new dr(this, t);
                        return e.on(Fo.POST_IMAGE_LOADED, this.onPostImageLoaded.bind(this)), e.on(Fo.POST_IMAGE_FAILED, this.onPostImageFailed.bind(this)), this.trigger(Fo.AD_CREATED, e), e;
                    }),
                    (e.prototype.checkDeepLink = function () {
                        var t = this;
                        if (this.config("popup.deepLink")) {
                            var e = window.document.location.hash;
                            if ((e = e.replace("#", "")).indexOf("crt:post:") > -1) {
                                var n = e.substring("crt:post:".length);
                                this.feed.loadPost(n).then(function (e) {
                                    t.popupManager.showPopup(e.post);
                                });
                            }
                        }
                    }),
                    (e.prototype.onPostsLoaded = function (t, e) {
                        to.log("BaseWidget->onPostsLoaded"), to.log(t.length), to.log(e);
                    }),
                    (e.prototype.onPostsFail = function (t) {
                        to.log("BaseWidget->onPostsLoadedFail"), to.log(t);
                    }),
                    (e.prototype.onPostClick = function (t, e) {
                        to.log("BaseWidget->onPostClick"),
                            to.log(t),
                            to.log(e),
                            this.trigger(Fo.POST_CLICK, e),
                            this.trackPostAction(e.json.id, "click"),
                            this.config("post.clickAction") === r.POST_CLICK_ACTION_OPEN_POPUP
                                ? this.popupManager.showPopup(e.json)
                                : this.config("post.clickAction") === r.POST_CLICK_ACTION_GOTO_SOURCE && "" !== e.json.url && window.open(e.json.url);
                    }),
                    (e.prototype.onPostClickReadMore = function (t, e) {
                        to.log("BaseWidget->onPostClickReadMore"),
                            to.log(t),
                            to.log(e),
                            this.trigger(Fo.POST_CLICK_READ_MORE, e),
                            this.config("post.clickReadMoreAction") === r.POST_CLICK_ACTION_OPEN_POPUP
                                ? this.popupManager.showPopup(e.json)
                                : this.config("post.clickReadMoreAction") === r.POST_CLICK_ACTION_GOTO_SOURCE && window.open(e.json.url);
                    }),
                    (e.prototype.onPostImageLoaded = function (t) {}),
                    (e.prototype.onPostImageFailed = function (t) {}),
                    (e.prototype.onFeedLoaded = function (t) {
                        this.config("hidePoweredBy") && t.account && 1 === t.account.plan.unbranded ? this.$container.addClass("crt-widget-branded") : this.$container.addClass("crt-widget-branded");
                    }),
                    (e.prototype.track = function (t) {
                        to.log("Feed->track " + t), this.tracker.track(t);
                    }),
                    (e.prototype.trackPostAction = function (t, e) {
                        to.log("Feed->trackPostAction " + t + " " + e), this.tracker.trackPostAction(t, e);
                    }),
                    (e.prototype.getUrl = function (t) {
                        return (this.feedBase = this.config("feed.apiEndpoint") + "/restricted/feed"), this.feedBase + t;
                    }),
                    (e.prototype._t = function (t) {
                        return Ao.t(t);
                    }),
                    (e.prototype.reload = function () {
                        to.log("BaseWidget->reload");
                    }),
                    (e.prototype.checkPoweredBy = function () {
                        return this.$container.text().trim().indexOf("jug_fury") > -1 ||
                            (oo(".crt-logo").length && oo(".crt-logo").text().trim().indexOf("jug_fury") > -1) ||
                            (oo(".crt-tag").length && oo(".crt-tag").text().trim().indexOf("jug_fury") > -1)
                            ? ((this.hasPoweredBy = !0), !0)
                            : ((this.hasPoweredBy = !1), !1);
                    }),
                    (e.prototype.showNoPostsMessage = function () {
                        -1 !== ["curator-admin.test", "admin.curator.io", "admin-stage.curator.io", "superadmin.curator.io", "app.curator.io", "localhost"].indexOf(window.location.hostname) && this.showMessage("The feed contains no posts");
                    }),
                    (e.prototype.showMessage = function (t, e) {
                        void 0 === e && (e = "info"), (e = e || "info"), this.removeMessage(), (this.$message = oo('<div class="crt-notice crt-notice-' + e + '"><span>' + t + "</span></div>")), this.$el.append(this.$message);
                    }),
                    (e.prototype.removeMessage = function () {
                        this.$message && this.$message.remove();
                    }),
                    (e.prototype.startAutoLoad = function () {
                        to.log("BaseWidget->startAutoLoad"), (this.autoLoading = !0), (this.autoLoadTimeout = window.setTimeout(this.onAutoLoadFire.bind(this), 3e4));
                    }),
                    (e.prototype.stopAutoLoad = function () {
                        to.log("BaseWidget->stopAutoLoad"), (this.autoLoading = !1), window.clearTimeout(this.autoLoadTimeout);
                    }),
                    (e.prototype.onAutoLoadFire = function () {
                        to.log("BaseWidget->onAutoLoadFire"), this.feed.loadBefore(), (this.autoLoadTimeout = window.setTimeout(this.onAutoLoadFire.bind(this), 3e4));
                    }),
                    (e.prototype.destroy = function () {
                        if (
                            (to.log("BaseWidget->destroy"),
                            this.feed && this.feed.destroy(),
                            this.filter && this.filter.destroy(),
                            this.popupManager && this.popupManager.destroy(),
                            this.sheet && (this.clearStyles(), delete this.sheet),
                            this.$container.removeClass("crt-widget"),
                            this.$container.removeClass("crt-widget-unbranded"),
                            this.$container.removeClass("crt-widget-branded"),
                            this.$container.removeClass("crt-no-touch"),
                            this.$container[0].classList)
                        )
                            for (var e = 0; e < this.$container[0].classList.length; e++) {
                                var n = this.$container[0].classList[e];
                                0 === n.indexOf("crt-widget-theme-") && this.$container.removeClass(n);
                            }
                        t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(er),
            Mi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            Ri = (function (t) {
                function e() {
                    return (null !== t && t.apply(this, arguments)) || this;
                }
                return (
                    Mi(e, t),
                    (e.prototype.setStyles = function (t) {
                        this.createStleSheet(), this.setStylesGeneral(t);
                    }),
                    (e.prototype.setStylesGeneral = function (t) {
                        this.addStyle(t.popup, ".crt-popup"),
                            this.addStyle(t.widget, ".crt-widget"),
                            this.addStyle(t.loadMore, ".crt-widget .crt-load-more"),
                            this.addStyle(t.post, ".crt-widget .crt-post"),
                            this.addStyle(t.postText, ".crt-widget .crt-post-text"),
                            this.addStyle(t.postTextLink, ".crt-widget .crt-post-text a"),
                            this.addStyle(t.postName, ".crt-widget .crt-post-fullname a"),
                            this.addStyle(t.postUsername, ".crt-widget .crt-post-username a"),
                            this.addStyle(t.postIcon, ".crt-widget .crt-social-icon i"),
                            this.addStyle(t.postComments, ".crt-widget .crt-comments-likes"),
                            this.addStyle(t.postShareIcons, ".crt-widget .crt-post-footer .crt-post-share a"),
                            this.addStyle(t.postDate, ".crt-widget .crt-post-date a"),
                            this.addStyle(t.postMaxHeightReadMore, ".crt-widget .crt-post.crt-post-max-height .crt-post-max-height-read-more"),
                            this.addStyle(t.gridPost, ".crt-widget .crt-grid-post");
                    }),
                    e
                );
            })(Hi),
            Wi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            Gi = (function (t) {
                function e(e) {
                    var n = t.call(this, e, Tn) || this;
                    return (
                        (n._postsVisible = []),
                        (n._postsLoadIndex = -1),
                        (n._progressiveLoad = !1),
                        (n.currentPage = 0),
                        (n._config.feed.limit = n._config.widget.postsPerPage || 12),
                        (n._postsVisible = []),
                        (n._postsLoadIndex = -1),
                        to.log("Waterfall->init"),
                        (n._progressiveLoad = n.config("widget.progressiveLoad", !1)),
                        n.renderWidget(),
                        n.$container.append(n.$el),
                        n.$container.addClass("crt-widget-waterfall"),
                        n.checkRefs(["feed", "loadMore"]),
                        n.$refs.loadMore.hide(),
                        (n.ui = new Zo(n, n.$refs.feed)),
                        n.iniListeners(),
                        n.load(),
                        n
                    );
                }
                return (
                    Wi(e, t),
                    (e.prototype.iniListeners = function () {
                        var t = this;
                        this.config("widget.continuousScroll") &&
                            this.$el.on("scroll", function () {
                                var e = t.$el.height(),
                                    n = t.$refs.feed.height();
                                t.$el.scrollTop() >= n - e && t.onMoreClick();
                            }),
                            this.config("widget.autoLoadNew") && this.startAutoLoad();
                    }),
                    (e.prototype.destroyListeners = function () {
                        this.stopAutoLoad();
                    }),
                    (e.prototype.onMoreClick = function () {
                        this.log("onMoreClick"), this.load();
                    }),
                    (e.prototype.reload = function () {
                        this.log("reload"), this.removePosts(), this.feed.reset(), this.ui.empty(), this.load();
                    }),
                    (e.prototype.load = function () {
                        var t = this;
                        this.log("load"),
                            this.feed.loading ||
                                ((this._postsLoadIndex = 0),
                                (this._postsVisible = []),
                                this.removeMessage(),
                                this.feed
                                    .loadXPosts(this.config("widget.postsPerPage"))
                                    .then(function (e) {
                                        if (t.alive)
                                            if (0 === t.feed.numPosts()) t.showNoPostsMessage(), t.$refs.loadMore.hide();
                                            else {
                                                t.feed.numPosts() > 0 && t.removeMessage();
                                                for (var n = 0; n < e.length; n++) {
                                                    var o = t.createElement(e[n]);
                                                    t.ui.append(o), t._postsVisible.push(o);
                                                }
                                                t._progressiveLoad && t._loadNextPostImage(0), t.config("widget.showLoadMore") && t._hasMorePostsToShow() ? t.$refs.loadMore.show() : t.$refs.loadMore.hide();
                                            }
                                    })
                                    .catch(function (e) {
                                        throw (e && e.message ? t.showMessage(e.message, "error") : t.showMessage("Feed failed to load, check browser console for more info", "error"), e);
                                    }));
                    }),
                    (e.prototype.loadBefore = function () {
                        this.log("loadBefore"), this.feed.loadBefore();
                    }),
                    (e.prototype.loadPage = function (t) {
                        var e = this;
                        this.log("loadPage"),
                            this.feed.loading ||
                                (this.removePosts(),
                                this.ui.empty(),
                                (this._postsLoadIndex = -1),
                                (this.currentPage = t),
                                this.feed.loadPage(t).then(function (t) {
                                    if ("boolean" != typeof t) {
                                        for (var n = 0, o = t.posts; n < o.length; n++) {
                                            var r = o[n],
                                                i = e.createElement(r);
                                            e.ui.append(i), e._postsVisible.push(i);
                                        }
                                        e._progressiveLoad && e._loadNextPostImage(0);
                                    }
                                }));
                    }),
                    (e.prototype.onPostsLoaded = function (t, e) {
                        to.log("Waterfall->onPostsLoaded " + e);
                    }),
                    (e.prototype.onPostImageLoaded = function () {
                        this._progressiveLoad && this._loadNextPostImage(this._postsLoadIndex + 1);
                    }),
                    (e.prototype.onPostImageFailed = function () {
                        this._progressiveLoad && this._loadNextPostImage(this._postsLoadIndex + 1);
                    }),
                    (e.prototype._loadNextPostImage = function (t) {
                        t < this._postsVisible.length && (this._postsVisible[t].loadImage(), (this._postsLoadIndex = t));
                    }),
                    (e.prototype._hasMorePostsToShow = function () {
                        return !this.feed.allPostsLoaded;
                    }),
                    (e.prototype.log = function (t) {
                        var e = "Waterfall[" + this.id + "]";
                        to.log(e + "->" + t);
                    }),
                    (e.prototype.removePosts = function () {
                        this.log("removePosts");
                        for (var t = 0; t < this._postsVisible.length; t++) this._postsVisible[t].destroy();
                        this._postsVisible = [];
                    }),
                    (e.prototype.destroy = function () {
                        this.log("destroy"), this.removePosts(), this.destroyListeners(), this.ui && this.ui.destroy(), this.$container.removeClass("crt-widget-waterfall"), t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(Ri),
            zi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            Vi = (function (t) {
                function e(e, n) {
                    var o = t.call(this, e, n) || this;
                    if ((o.render(), o.widget.config("post.imageHeight", "100%"))) {
                        var r = o.widget.config("post.imageHeight", "100%");
                        o.$refs.spacer.css("padding-bottom", r);
                    }
                    return o.setupVideo(), o.setupCarousel(), o.setupShare(), o;
                }
                return (
                    zi(e, t),
                    (e.prototype.layout = function () {
                        this.layoutFooter(), this.trigger(Fo.POST_LAYOUT_CHANGED, this);
                    }),
                    (e.prototype.onWidgetResize = function () {
                        this.layoutFooter();
                    }),
                    (e.prototype.layoutFooter = function () {
                        if (!this.$el.hasClass("crt-grid-post-new-york")) {
                            var t = this.$el.find(".crt-post-username"),
                                e = this.$el.find(".crt-date"),
                                n = this.$el.find(".crt-post-footer"),
                                o = this.$el.find(".crt-post-share"),
                                r = this.$el.find(".crt-post-userimage"),
                                i = n.width();
                            t.width() + e.width() + o.width() + r.width() + 40 > i && t.hide();
                        }
                    }),
                    e
                );
            })(ir),
            Ui = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            Zi = (function (t) {
                function e(e, n) {
                    var o = t.call(this, e, n) || this;
                    if (((o.reqCount = 0), (o.raf = 0), (o.templateId = "ad-grid"), o.render(), o.widget.config("post.imageHeight", "100%"))) {
                        var r = o.widget.config("post.imageHeight", "100%");
                        o.$refs.spacer.css("padding-bottom", r);
                    }
                    return o;
                }
                return (
                    Ui(e, t),
                    (e.prototype.layout = function () {
                        this.layoutFooter(), this.trigger(Fo.POST_LAYOUT_CHANGED, this);
                    }),
                    (e.prototype.onWidgetResize = function () {
                        this.layoutFooter();
                    }),
                    (e.prototype.layoutFooter = function () {
                        if (!this.$el.hasClass("crt-grid-post-new-york")) {
                            var t = this.$el.find(".crt-post-username"),
                                e = this.$el.find(".crt-date"),
                                n = this.$el.find(".crt-post-footer"),
                                o = this.$el.find(".crt-post-share"),
                                r = this.$el.find(".crt-post-userimage"),
                                i = n.width();
                            t.width() + e.width() + o.width() + r.width() + 40 > i && t.hide();
                        }
                    }),
                    e
                );
            })(ur),
            Xi = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            qi = (function (t) {
                function e() {
                    return (null !== t && t.apply(this, arguments)) || this;
                }
                return (
                    Xi(e, t),
                    (e.prototype.setStyles = function (t) {
                        this.sheet ? this.clearStyles() : (this.sheet = io.createSheet()),
                            this.addStyle(t.popup, ".crt-popup"),
                            this.addStyle(t.widget, ".crt-widget"),
                            this.addStyle(t.loadMore, ".crt-widget .crt-load-more"),
                            this.addStyle(t.gridPostIcon, ".crt-widget .crt-social-icon i"),
                            this.addStyle(t.gridPost, ".crt-widget .crt-grid-post"),
                            this.addStyle(t.gridPostContent, ".crt-widget .crt-grid-post-content"),
                            this.addStyle(t.gridPostText, ".crt-widget .crt-grid-post .crt-post-text"),
                            this.addStyle(t.gridPostText, ".crt-widget .crt-grid-post .crt-post-title"),
                            this.addStyle(t.gridPostTextLink, ".crt-widget .crt-grid-post .crt-post-text a"),
                            this.addStyle(t.gridPostHover, ".crt-widget .crt-grid-post .crt-post-hover"),
                            this.addStyle(t.gridPostHoverLink, ".crt-widget .crt-grid-post .crt-post-hover a"),
                            this.addStyle(t.gridPostFooter, ".crt-widget .crt-grid-post .crt-post-footer"),
                            this.addStyle(t.gridPostDate, ".crt-widget .crt-date");
                    }),
                    e
                );
            })(Hi),
            Ki = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            Ji = function (t, e, n, o) {
                return new (n || (n = Promise))(function (r, i) {
                    function s(t) {
                        try {
                            c(o.next(t));
                        } catch (t) {
                            i(t);
                        }
                    }
                    function a(t) {
                        try {
                            c(o.throw(t));
                        } catch (t) {
                            i(t);
                        }
                    }
                    function c(t) {
                        var e;
                        t.done
                            ? r(t.value)
                            : ((e = t.value),
                              e instanceof n
                                  ? e
                                  : new n(function (t) {
                                        t(e);
                                    })).then(s, a);
                    }
                    c((o = o.apply(t, e || [])).next());
                });
            },
            Yi = function (t, e) {
                var n,
                    o,
                    r,
                    i,
                    s = {
                        label: 0,
                        sent: function () {
                            if (1 & r[0]) throw r[1];
                            return r[1];
                        },
                        trys: [],
                        ops: [],
                    };
                return (
                    (i = { next: a(0), throw: a(1), return: a(2) }),
                    "function" == typeof Symbol &&
                        (i[Symbol.iterator] = function () {
                            return this;
                        }),
                    i
                );
                function a(i) {
                    return function (a) {
                        return (function (i) {
                            if (n) throw new TypeError("Generator is already executing.");
                            for (; s; )
                                try {
                                    if (((n = 1), o && (r = 2 & i[0] ? o.return : i[0] ? o.throw || ((r = o.return) && r.call(o), 0) : o.next) && !(r = r.call(o, i[1])).done)) return r;
                                    switch (((o = 0), r && (i = [2 & i[0], r.value]), i[0])) {
                                        case 0:
                                        case 1:
                                            r = i;
                                            break;
                                        case 4:
                                            return s.label++, { value: i[1], done: !1 };
                                        case 5:
                                            s.label++, (o = i[1]), (i = [0]);
                                            continue;
                                        case 7:
                                            (i = s.ops.pop()), s.trys.pop();
                                            continue;
                                        default:
                                            if (!((r = s.trys), (r = r.length > 0 && r[r.length - 1]) || (6 !== i[0] && 2 !== i[0]))) {
                                                s = 0;
                                                continue;
                                            }
                                            if (3 === i[0] && (!r || (i[1] > r[0] && i[1] < r[3]))) {
                                                s.label = i[1];
                                                break;
                                            }
                                            if (6 === i[0] && s.label < r[1]) {
                                                (s.label = r[1]), (r = i);
                                                break;
                                            }
                                            if (r && s.label < r[2]) {
                                                (s.label = r[2]), s.ops.push(i);
                                                break;
                                            }
                                            r[2] && s.ops.pop(), s.trys.pop();
                                            continue;
                                    }
                                    i = e.call(t, s);
                                } catch (t) {
                                    (i = [6, t]), (o = 0);
                                } finally {
                                    n = r = 0;
                                }
                            if (5 & i[0]) throw i[1];
                            return { value: i[0] ? i[1] : void 0, done: !0 };
                        })([i, a]);
                    };
                }
            },
            Qi = (function (t) {
                function e(e) {
                    var n = t.call(this, e, Fn) || this;
                    return (
                        (n._loading = !1),
                        (n.columnCount = 0),
                        (n.rowCount = 0),
                        (n._postsRendered = []),
                        n.config("post.minWidth") < 100 && $i(n._config, "post.minWidth", 100),
                        $i(n._config, "feed.postsPerPage", 25),
                        (n.templateId = n.config("widget.template")),
                        n.render(),
                        n.$container.append(n.$el),
                        (n.$scroller = oo(window)),
                        n.$container.addClass("crt-grid"),
                        n.$container.addClass("crt-widget-grid"),
                        n.config("widget.showLoadMore") ? n.$refs.feedWindow.css({ position: "relative" }) : n.$refs.loadMore.hide(),
                        (n.rowCount = n.config("widget.rows")),
                        n.createHandlers(),
                        n
                    );
                }
                return (
                    Ki(e, t),
                    (e.prototype.createHandlers = function () {
                        var t = this,
                            e = this.id;
                        (this.resize = Vo.debounce(this.resize, 100, this)),
                            (this.ro = new Lo.a(function (e) {
                                e.length > 0 && t.resize();
                            })),
                            this.ro.observe(this.$container[0]),
                            oo(window).on("curatorCssLoaded." + e, this.resize.bind(this)),
                            this.config("widget.continuousScroll") &&
                                oo(window).on(
                                    "scroll." + e,
                                    Vo.debounce(
                                        function () {
                                            t.checkScroll();
                                        },
                                        100,
                                        this
                                    )
                                ),
                            this.config("widget.autoLoadNew") && this.startAutoLoad();
                    }),
                    (e.prototype.destroyHandlers = function () {
                        this.log("destroyHandlers");
                        var t = this.id;
                        oo(window).off("curatorCssLoaded." + t), oo(document).off("ready." + t), oo(window).off("scroll." + t), this.ro && this.ro.disconnect(), this.stopAutoLoad();
                    }),
                    (e.prototype.reload = function () {
                        to.log("Grid->reload"), this.$refs.feed.find(".crt-grid-post").remove(), (this.rowCount = this.config("widget.rows")), (this._postsRendered = []), this.feed.reset(), this.renderRows();
                    }),
                    (e.prototype.loadBefore = function () {
                        return Ji(this, void 0, void 0, function () {
                            return Yi(this, function (t) {
                                switch (t.label) {
                                    case 0:
                                        return to.log("Grid->loadBefore"), [4, this.feed.loadBefore()];
                                    case 1:
                                        return [2, t.sent()];
                                }
                            });
                        });
                    }),
                    (e.prototype.resize = function () {
                        this.log("resize"), this.updateResponsiveOptions(), this.renderRows(), this.trigger(Fo.WIDGET_RESIZE);
                    }),
                    (e.prototype.renderRows = function () {
                        this.log("renderRows");
                        var t = Math.floor(this.$container.width() / this.config("post.minWidth"));
                        (t = t < 1 ? 1 : t),
                            this._loading ||
                                (this.$container.removeClass("crt-grid-col" + this.columnCount),
                                (this.columnCount = t),
                                this.$container.addClass("crt-grid-col" + this.columnCount),
                                this.feed.isFirstLoad() || this.feed.hasMorePosts() || this._postsRendered.length < this.feed.numPosts() ? this._loadPosts(t) : this.updateHeight());
                    }),
                    (e.prototype._loadPosts = function (t) {
                        var e = this;
                        this.log("_loadPosts " + t), (this._loading = !0);
                        var n = t * this.rowCount - this._postsRendered.length;
                        this.feed
                            .loadXPosts(n)
                            .then(function (t) {
                                for (var n = 0; n < t.length; n++) {
                                    var o = e.createElement(t[n]);
                                    e.$refs.feed.append(o.$el), o.layout(), o.fadeIn(n), e._postsRendered.push(o);
                                }
                                (e._loading = !1),
                                    (e.updateHeightTimeout = window.setTimeout(function () {
                                        e.updateHeight();
                                    }, 10));
                            })
                            .catch(function (t) {
                                throw (t.message ? e.showMessage(t.message, "error") : e.showMessage("Feed failed to load, check browser console for more info", "error"), t);
                            });
                    }),
                    (e.prototype.createPostElement = function (t) {
                        var e = new Vi(this, t);
                        return (
                            e.on(Fo.POST_CLICK, this.onPostClick.bind(this)),
                            e.on(Fo.POST_CLICK_READ_MORE, this.onPostClickReadMore.bind(this)),
                            e.on(Fo.POST_IMAGE_LOADED, this.onPostImageLoaded.bind(this)),
                            this.trigger(Fo.POST_CREATED, e),
                            e
                        );
                    }),
                    (e.prototype.createAdElement = function (t) {
                        var e = new Zi(this, t);
                        return this.trigger(Fo.AD_CREATED, e), e;
                    }),
                    (e.prototype.updateHeight = function () {
                        if ((this.log("updateHeight"), this.alive)) {
                            var t = Math.ceil(this.feed.numPosts() / this.columnCount),
                                e = this.rowCount < t ? this.rowCount : t,
                                n = this._postsRendered[0];
                            if (n) {
                                var o = n.getHeight();
                                this.$refs.feedWindow.css({ overflow: "hidden" });
                                var r = this.$scroller.scrollTop();
                                this.$refs.feedWindow.height(e * o), this.$scroller.scrollTop() > r + 100 && this.$scroller.scrollTop(r);
                            }
                            this.config("widget.showLoadMore") && this._hasMorePostsToShow(e) ? this.$refs.loadMore.show() : this.$refs.loadMore.hide(), this.trigger(Fo.GRID_HEIGHT_CHANGED, this);
                        }
                    }),
                    (e.prototype.checkScroll = function () {
                        this.log("checkScroll");
                        var t = this.$container.offset().top + this.$refs.feedWindow.height();
                        this.$scroller.scrollTop() + oo(window).height() - t > this.config("widget.continuousScrollOffset") &&
                            (this.feed.loading || this.feed.allPostsLoaded || ((this.rowCount += this.config("widget.loadMoreRows")), this.renderRows()));
                    }),
                    (e.prototype.onMoreClick = function () {
                        this.log("onMoreClick");
                        var t = this.config("widget.loadMoreRows");
                        (this.rowCount += t), this.renderRows();
                    }),
                    (e.prototype.log = function (t) {
                        var e = "Grid[" + this.id + "]";
                        to.log(e + "->" + t);
                    }),
                    (e.prototype._hasMorePostsToShow = function (t) {
                        var e = this.columnCount * t;
                        return !this.feed.allPostsLoaded || e < this.feed.numPosts();
                    }),
                    (e.prototype.destroy = function () {
                        this.log("destroy"),
                            this.destroyHandlers(),
                            this.$container
                                .removeClass("crt-widget-grid")
                                .removeClass("crt-grid")
                                .removeClass("crt-grid-col" + this.columnCount)
                                .css({ height: "", overflow: "" }),
                            window.clearTimeout(this.updateHeightTimeout),
                            t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(qi),
            ts = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            es = (function (t) {
                function e(e) {
                    var n = t.call(this) || this;
                    return (n.posts = []), (n.paneIndex = e), (n.posts = []), (n._$el = oo('<div class="crt-carousel-pane"><div class="crt-pane-index">' + n.paneIndex + "</div> </div>")), n;
                }
                return (
                    ts(e, t),
                    (e.prototype.addPost = function (t) {
                        t.on(Fo.POST_LAYOUT_CHANGED, this.onPostLayoutChanged.bind(this)),
                            t.on(Fo.POST_IMAGE_LOADED, this.onPostImageLoaded.bind(this)),
                            t.on(Fo.POST_IMAGE_FAILED, this.onPostImageFailed.bind(this)),
                            this.$el.append(t.$el),
                            this.posts.push(t);
                    }),
                    (e.prototype.onPostImageLoaded = function () {
                        this.trigger(Fo.POST_IMAGE_LOADED);
                    }),
                    (e.prototype.onPostImageFailed = function () {
                        this.trigger(Fo.POST_IMAGE_FAILED);
                    }),
                    (e.prototype.onPostLayoutChanged = function () {
                        this.trigger(Fo.PANE_HEIGHT_CHANGED);
                    }),
                    (e.prototype.getHeight = function () {
                        for (var t = 0, e = 0, n = this.posts; e < n.length; e++) {
                            t += n[e].getHeight();
                        }
                        return t;
                    }),
                    (e.prototype.forceHeight = function (t) {
                        for (var e = 0, n = this.posts; e < n.length; e++) {
                            n[e].forceHeight(t);
                        }
                        return this.$el.height();
                    }),
                    (e.prototype.loadImage = function () {
                        for (var t = 0; t < this.posts.length; t++) this.posts[t].loadImage();
                    }),
                    (e.prototype.destroy = function () {
                        for (var e = 0, n = this.posts; e < n.length; e++) {
                            n[e].destroy();
                        }
                        t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(er),
            ns = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            os = (function (t) {
                function e(e, n, o, r) {
                    var i = t.call(this) || this;
                    return (
                        (i._currentPane = 0),
                        (i._moving = !1),
                        (i.PANES_VISIBLE = 0),
                        (i.PANES_LENGTH = 0),
                        (i.PANE_WIDTH = 0),
                        (i.VIEWPORT_WIDTH = 0),
                        (i.renderMode = "-"),
                        (i.paneCache = {}),
                        (i.currentPanes = []),
                        (i.autoPlayTimeout = 0),
                        (i.postLayoutChangedTO = 0),
                        (i.widget = e),
                        (i.$viewport = n),
                        (i.$stage = o),
                        (i.$slider = r),
                        (i.$viewport = n),
                        (i.$stage = o),
                        (i.$slider = r),
                        i.widget.config("post.matchHeights", !1) && i.$viewport.addClass("crt-match-heights"),
                        i.widget.config("widget.controlsOver") ? i.$viewport.addClass("crt-controls-over") : i.$viewport.addClass("crt-controls-outside"),
                        i.widget.config("widget.controlsShowOnHover") && i.$viewport.addClass("crt-controls-show-on-hover"),
                        i
                    );
                }
                return (
                    ns(e, t),
                    (e.prototype.createHandlers = function () {
                        var t = this;
                        (this.onResize = Go(this.onResize.bind(this), 100)),
                            (this.ro = new Lo.a(function (e) {
                                e.length > 0 && t.onResize();
                            })),
                            this.ro.observe(this.$viewport[0]);
                    }),
                    (e.prototype.calcSizes = function () {
                        if ((this.log("calcSizes"), (this.VIEWPORT_WIDTH = this.$viewport.width()), to.log("VIEWPORT_WIDTH = " + this.VIEWPORT_WIDTH), !isNaN(this.VIEWPORT_WIDTH) && this.VIEWPORT_WIDTH > 0)) {
                            var t = this.widget.config("widget.panesVisible");
                            if ("number" == typeof t && t > 0) (this.PANES_VISIBLE = t), (this.PANE_WIDTH = this.VIEWPORT_WIDTH / this.PANES_VISIBLE);
                            else {
                                var e = this.widget.config("post.minWidth");
                                (this.PANES_VISIBLE = this.VIEWPORT_WIDTH < e ? 1 : Math.floor(this.VIEWPORT_WIDTH / e)), (this.PANE_WIDTH = this.VIEWPORT_WIDTH / this.PANES_VISIBLE);
                            }
                        }
                    }),
                    (e.prototype.getPane = function (t) {
                        var e;
                        return (
                            this.paneCache["idx" + t] ? (e = this.paneCache["idx" + t]) : ((e = this.widget.createPane(t)).on(Fo.PANE_HEIGHT_CHANGED, this.onPaneHeightChanged.bind(this)), (this.paneCache["idx" + t] = e)),
                            (e.paneIndex = "idx" + t),
                            e
                        );
                    }),
                    (e.prototype.onResize = function () {
                        console.log("STUB->onResize()");
                    }),
                    (e.prototype.canRotate = function () {
                        return this.PANES_LENGTH > this.PANES_VISIBLE;
                    }),
                    (e.prototype.controlsHideShow = function () {
                        this.canRotate() ? this.$viewport.removeClass("crt-hide-controls") : this.$viewport.addClass("crt-hide-controls");
                    }),
                    (e.prototype.next = function () {
                        if (!this._moving) {
                            var t = this.widget.config("widget.moveAmount"),
                                e = t || this.PANES_VISIBLE;
                            this.move(e, !1);
                        }
                    }),
                    (e.prototype.prev = function () {
                        if (!this._moving) {
                            var t = this.widget.config("widget.moveAmount"),
                                e = t || this.PANES_VISIBLE;
                            this.move(0 - e, !1);
                        }
                    }),
                    (e.prototype.move = function (t, e) {
                        console.log("STUB->move " + t + " " + e);
                    }),
                    (e.prototype.moveToX = function (t, e) {
                        if (e) this.$slider.removeClass("crt-animate-transform"), this.setSliderX(t), this.moveComplete();
                        else {
                            var n = { duration: this.widget.config("widget.duration"), complete: this.moveComplete.bind(this) };
                            no() && (n.easing = this.widget.config("widget.easing", "ease-in-out")),
                                no()
                                    ? (this.$slider.addClass("crt-animate-transform"), this.$slider.animate({ transform: "translate3d(" + t + "px, 0px, 0px)" }, n))
                                    : ((n.step = function (t, e) {
                                          "crtTransformX" === e.prop &&
                                              (oo(this).css("-webkit-transform", "translate3d(" + t + "px, 0px, 0px)"),
                                              oo(this).css("-moz-transform", "translate3d(" + t + "px, 0px, 0px)"),
                                              oo(this).css("transform", "translate3d(" + t + "px, 0px, 0px)"));
                                      }),
                                      this.$slider.addClass("crt-animate-transform"),
                                      this.$slider.animate({ crtTransformX: t }, n));
                        }
                    }),
                    (e.prototype.moveComplete = function () {
                        console.log("STUB->move");
                    }),
                    (e.prototype.updateHeight = function (t) {
                        void 0 === t && (t = !0), this.log("updateHeight");
                        var e = this.getMaxHeight();
                        this.$stage.height() !== e && (t ? this.$stage.animate({ height: e }, 300) : this.$stage.css({ height: e })), this.widget.config("post.matchHeights") && this.setPaneHeights();
                    }),
                    (e.prototype.setPaneHeights = function () {
                        if ((this.log("setPaneHeights "), this.widget.config("post.matchHeights")))
                            for (var t = this.getMaxHeight(), e = 0, n = this.currentPanes; e < n.length; e++) {
                                n[e].forceHeight(t);
                            }
                    }),
                    (e.prototype.getMaxHeight = function () {
                        return this.log("STUB->getMaxHeight "), 100;
                    }),
                    (e.prototype.setSliderX = function (t) {
                        this.$slider.css({ transform: "translate3d(" + t + "px, 0px, 0px)" });
                    }),
                    (e.prototype.onPaneHeightChanged = function () {
                        var t = this;
                        this._moving ||
                            (window.clearTimeout(this.postLayoutChangedTO),
                            (this.postLayoutChangedTO = window.setTimeout(function () {
                                t.updateHeight();
                            }, 100)));
                    }),
                    (e.prototype.log = function (t) {
                        to.log("LayoutCarouselBase->" + t);
                    }),
                    e
                );
            })(o),
            rs = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            is = (function (t) {
                function e(e, n, o, r) {
                    var i = t.call(this, e, n, o, r) || this;
                    return (i.moveCompleteTO = 0), i.log("construct"), (i._currentPane = 0), (i._moving = !1), (i.PANES_VISIBLE = 0), (i.PANES_LENGTH = 0), (i.PANE_WIDTH = 0), i.controlsHideShow(), i.createHandlers(), i;
                }
                return (
                    rs(e, t),
                    (e.prototype.currentPane = function () {
                        return this._currentPane;
                    }),
                    (e.prototype.numVisiblePanes = function () {
                        return this.PANES_VISIBLE;
                    }),
                    (e.prototype.debug = function () {
                        return "";
                    }),
                    (e.prototype.setPanesLength = function (t, e, n) {
                        void 0 === e && (e = !1), void 0 === n && (n = 0), this.log("setPanesLength " + t), (n = n || 0);
                        var o = 0 === this.PANES_LENGTH;
                        if (((this.PANES_LENGTH = t), this.renderPanes(), o || e)) {
                            this.calcSizes(), (this._currentPane = n);
                            var r = this.getX();
                            this.$slider.width(this.PANES_LENGTH * this.PANE_WIDTH), this.setSliderX(r), this.updateHeight(), this.widget.config("widget.autoPlay") && this.autoPlayStart(), this.controlsHideShow();
                        }
                    }),
                    (e.prototype.renderPanes = function () {
                        if ((this.log("renderPanes"), !(this.currentPanes.length >= this.PANES_LENGTH)))
                            for (var t = this.currentPanes.length; t < this.PANES_LENGTH; t++) {
                                var e = this.getPane(t);
                                e.$el.css({ width: this.PANE_WIDTH + "px" }), this.$slider.append(e.$el), this.currentPanes.push(e);
                            }
                    }),
                    (e.prototype.resize = function () {
                        this.calcSizes(), this.$slider.width(this.PANES_LENGTH * this.PANE_WIDTH);
                        for (var t = 0; t < this.currentPanes.length; t++) this.currentPanes[t].$el.css({ width: this.PANE_WIDTH + "px" });
                        var e = this.getX();
                        this.setSliderX(e);
                    }),
                    (e.prototype.onResize = function () {
                        this.resize(), this.move(0, !0), this.updateHeight(!1), this.controlsHideShow(), this.widget.config("widget.autoPlay") && this.autoPlayStart();
                    }),
                    (e.prototype.getX = function () {
                        return 0 - this._currentPane * this.PANE_WIDTH;
                    }),
                    (e.prototype.canRotate = function () {
                        return this.PANES_VISIBLE < this.PANES_LENGTH;
                    }),
                    (e.prototype.autoPlayStart = function () {
                        var t = this;
                        window.clearTimeout(this.autoPlayTimeout),
                            this.canRotate() &&
                                (this.autoPlayTimeout = window.setTimeout(function () {
                                    t.next();
                                }, this.widget.config("widget.speed")));
                    }),
                    (e.prototype.move = function (t, e) {
                        this.log("move currentPane:" + this._currentPane + " moveAmt:" + t);
                        var n = this._currentPane + t;
                        if (
                            ((this._moving = !0),
                            this.widget.config("widget.infinite")
                                ? n < 0
                                    ? (n = this.PANES_LENGTH + n)
                                    : n > this.PANES_LENGTH && (n -= this.PANES_LENGTH)
                                : n < 0
                                ? (n = 0)
                                : n > this.PANES_LENGTH - this.PANES_VISIBLE && (n = this.PANES_LENGTH - this.PANES_VISIBLE),
                            (this._currentPane = n),
                            t)
                        ) {
                            var o = this.getX();
                            this.moveToX(o, e);
                        } else this._moving = !1;
                    }),
                    (e.prototype.moveComplete = function () {
                        var t = this;
                        this.log("moveComplete"),
                            this.alive &&
                                ((this._moving = !1),
                                this.trigger(Fo.CAROUSEL_CHANGED, this, this._currentPane),
                                this.widget.config("widget.autoPlay") && this.autoPlayStart(),
                                (this.moveCompleteTO = window.setTimeout(function () {
                                    t.log("moveComplete TO"), t.updateHeight();
                                }, 50)));
                    }),
                    (e.prototype.getMaxHeight = function () {
                        var t = this._currentPane,
                            e = t + this.PANES_VISIBLE;
                        return this.getMaxHeightForPanes(t, e);
                    }),
                    (e.prototype.getMaxHeightForPanes = function (t, e) {
                        this.log("getMaxHeightForPanes " + t + ":" + e);
                        for (var n = 0, o = t; o < e; o++)
                            if (this.currentPanes[o]) {
                                var r = this.currentPanes[o].getHeight();
                                r > n && (n = r);
                            }
                        return n;
                    }),
                    (e.prototype.reset = function () {
                        window.clearTimeout(this.autoPlayTimeout), window.clearTimeout(this.moveCompleteTO), this.$slider.empty(), this.setSliderX(0), (this.PANES_LENGTH = 0), (this._currentPane = 0), (this.currentPanes = []);
                    }),
                    (e.prototype.log = function (t) {
                        to.log("LayoutCarouselStatic->" + t);
                    }),
                    (e.prototype.destroy = function () {
                        this.log("destroy"), t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(os),
            ss = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })();
        var as = (function (t) {
                function e(e, n, o, r) {
                    var i = t.call(this, e, n, o, r) || this;
                    return (
                        (i._currentPane = 0),
                        (i._moving = !1),
                        (i.PANES_VISIBLE = 0),
                        (i.PANES_LENGTH = 0),
                        (i.PANE_WIDTH = 0),
                        (i.VIEWPORT_WIDTH = 0),
                        (i.autoPlayTimeout = 0),
                        (i.postLayoutChangedTO = 0),
                        (i.moveCompleteTO = 0),
                        (i.parentWidth = 0),
                        (i.paneCache = {}),
                        (i.currentPanes = []),
                        i.log("construct"),
                        (i.widget = e),
                        (i.$parent = i.$viewport.parent().parent()),
                        i.createHandlers(),
                        i
                    );
                }
                return (
                    ss(e, t),
                    (e.prototype.numVisiblePanes = function () {
                        return this.PANES_VISIBLE;
                    }),
                    (e.prototype.currentPane = function () {
                        return this._currentPane;
                    }),
                    (e.prototype.setPanesLength = function (t, e, n) {
                        void 0 === e && (e = !1), void 0 === n && (n = 0), this.log("setPanesLength " + t);
                        var o = 0 === this.PANES_LENGTH;
                        if (((this.PANES_LENGTH = t), o || e)) {
                            (this.paneCache = {}), (this._currentPane = n), this.updateParentWidth(), this.calcSizes(), this.updatePanes();
                            var r = this.getX();
                            this.setSliderWidth(), this.setSliderX(r), this.updateHeight(), this.widget.config("widget.autoPlay") && this.autoPlayStart(), this.controlsHideShow();
                        }
                    }),
                    (e.prototype.onResize = function () {
                        if (this.alive && !(this.PANES_LENGTH <= 0)) {
                            if ((this.log("onResize"), this.updateParentWidth())) {
                                var t = this.PANES_VISIBLE;
                                if ((this.calcSizes(), this.setSliderWidth(), t !== this.PANES_VISIBLE)) this.updatePanes();
                                else for (var e = 0; e < this.currentPanes.length; e++) this.currentPanes[e].$el.css({ width: this.PANE_WIDTH + "px" });
                                var n = this.getX();
                                this.setSliderX(n), this.move(0, !0);
                            }
                            this.updateHeight(!1), this.controlsHideShow(), this.widget.config("widget.autoPlay") && this.autoPlayStart();
                        }
                    }),
                    (e.prototype.updatePanes = function () {
                        this.log("updatePanes");
                        for (var t = 0; t < this.currentPanes.length; t++) {
                            (o = this.currentPanes[t]).$el.detach();
                        }
                        this.currentPanes = [];
                        for (var e = this.createPanes(), n = 0; n < e.length; n++) {
                            var o = e[n];
                            this.$slider.append(o.$el);
                        }
                        this.currentPanes = e;
                    }),
                    (e.prototype.createPanes = function () {
                        var t = [];
                        if (this.PANES_VISIBLE < this.PANES_LENGTH)
                            for (var e = this._currentPane - this.PANES_VISIBLE, n = 0; n < 3 * this.PANES_VISIBLE; n++) {
                                (o = this.getPane(e + n)).$el.css({ width: this.PANE_WIDTH + "px" }), t.push(o);
                            }
                        else
                            for (n = 0; n < this.PANES_LENGTH; n++) {
                                var o;
                                (o = this.getPane(n)).$el.css({ width: this.PANE_WIDTH + "px" }), t.push(o);
                            }
                        return t;
                    }),
                    (e.prototype.move = function (t, e) {
                        var n = this;
                        this.log("move currentPane:" + this._currentPane + " moveAmt:" + t + " noAnimate:" + e);
                        var o = this._currentPane,
                            r = this._currentPane + t;
                        if (
                            ((this._moving = !0),
                            this.widget.config("widget.infinite")
                                ? r < 0
                                    ? (r = this.PANES_LENGTH + r)
                                    : r > this.PANES_LENGTH && (r -= this.PANES_LENGTH)
                                : (r < 0 ? (r = 0) : r > this.PANES_LENGTH - this.PANES_VISIBLE && (r = this.PANES_LENGTH - this.PANES_VISIBLE), (t = r - o)),
                            (this._currentPane = r),
                            t)
                        ) {
                            var i = 0;
                            if ((this.canRotate() && (i = 0 - this.PANES_VISIBLE * this.PANE_WIDTH - t * this.PANE_WIDTH), e)) this.$slider.removeClass("crt-animate-transform"), this.setSliderX(i), this.moveComplete();
                            else {
                                var s = this.getX(),
                                    a = this.widget.config("widget.duration");
                                (function (t, e, n, o) {
                                    return new Promise(function (r) {
                                        var i = o / 1e3;
                                        (t.style.transform = "translate3d(" + e + "px, 0px, 0px)"),
                                            (t.style.transition = "-webkit-transform " + i + "s"),
                                            (t.style.transform = "translate3d(" + n + "px, 0px, 0px)"),
                                            setTimeout(function () {
                                                (t.style.transition = ""), (t.style.transform = "translate3d(" + n + "px, 0px, 0px)"), r();
                                            }, o + 10);
                                    });
                                })(this.$slider[0], s, i, a).then(function () {
                                    n.moveComplete();
                                });
                            }
                        } else this._moving = !1;
                    }),
                    (e.prototype.moveComplete = function () {
                        var t = this;
                        if ((this.log("moveComplete"), this.alive)) {
                            this.updatePanes();
                            var e = 0;
                            this.canRotate() && (e = 0 - this.PANES_VISIBLE * this.PANE_WIDTH),
                                this.setSliderX(e),
                                this.trigger(Fo.CAROUSEL_CHANGED, this, this._currentPane),
                                this.widget.config("widget.autoPlay") && this.autoPlayStart(),
                                (this.moveCompleteTO = window.setTimeout(function () {
                                    t.log("moveComplete TO"), t.updateHeight(), (t._moving = !1);
                                }, 20));
                        }
                    }),
                    (e.prototype.getMaxHeight = function () {
                        for (var t = 0, e = this.canRotate() ? this.PANES_VISIBLE : 0, n = e + this.PANES_VISIBLE, o = e; o < n; o++)
                            if (this.currentPanes[o]) {
                                var r = this.currentPanes[o].getHeight();
                                r > t && (t = r);
                            }
                        return t;
                    }),
                    (e.prototype.getX = function () {
                        return this.canRotate() ? 0 - this.PANES_VISIBLE * this.PANE_WIDTH : 0;
                    }),
                    (e.prototype.autoPlayStart = function () {
                        var t = this;
                        window.clearTimeout(this.autoPlayTimeout),
                            this.canRotate() &&
                                (this.autoPlayTimeout = window.setTimeout(function () {
                                    t.next();
                                }, this.widget.config("widget.speed")));
                    }),
                    (e.prototype.debug = function () {
                        for (var t = 0, e = 0; e < this.currentPanes.length; e++) {
                            var n = this.currentPanes[e].getHeight();
                            n > t && (t = n);
                        }
                        return t;
                    }),
                    (e.prototype.onPaneHeightChanged = function () {
                        var t = this;
                        this._moving ||
                            (window.clearTimeout(this.postLayoutChangedTO),
                            (this.postLayoutChangedTO = window.setTimeout(function () {
                                t.updateHeight();
                            }, 100)));
                    }),
                    (e.prototype.reset = function () {
                        window.clearTimeout(this.autoPlayTimeout),
                            window.clearTimeout(this.postLayoutChangedTO),
                            window.clearTimeout(this.moveCompleteTO),
                            this.$slider.empty(),
                            this.setSliderX(0),
                            (this.PANES_LENGTH = 0),
                            (this._currentPane = 0),
                            (this.paneCache = {}),
                            (this.currentPanes = []);
                    }),
                    (e.prototype.log = function (t) {
                        to.log("LayoutCarousel->" + t);
                    }),
                    (e.prototype.setSliderWidth = function () {
                        this.canRotate() ? this.$slider.width(this.PANE_WIDTH * (3 * this.PANES_VISIBLE)) : this.$slider.width(this.parentWidth);
                    }),
                    (e.prototype.updateParentWidth = function () {
                        var t = this.$parent.width();
                        return (t = t < 50 ? 50 : t), this.parentWidth !== t && ((this.parentWidth = t), !0);
                    }),
                    (e.prototype.destroyHandlers = function () {
                        this.ro && (this.log("ResizeObserver disconnect"), this.ro.disconnect());
                    }),
                    (e.prototype.destroy = function () {
                        for (var e in (this.log("destroy"), this.destroyHandlers(), window.clearTimeout(this.autoPlayTimeout), window.clearTimeout(this.postLayoutChangedTO), window.clearTimeout(this.moveCompleteTO), this.paneCache))
                            Object.prototype.hasOwnProperty.call(this.paneCache, e) && this.paneCache[e].destroy();
                        this.$stage.removeClass("crt-match-heights"),
                            this.widget.$container.removeClass("crt-controls-over"),
                            this.widget.$container.removeClass("crt-controls-show-on-hover"),
                            (this.paneCache = {}),
                            (this.currentPanes = []),
                            t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(os),
            cs = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            us = (function (t) {
                function e(e) {
                    var n = t.call(this, e, Sn) || this;
                    (n._lazyLoad = !1),
                        (n._progressiveLoad = !1),
                        (n.allLoaded = !1),
                        (n._panesVisible = []),
                        (n.posts = []),
                        (n._config.feed.limit = 50),
                        (n._lazyLoad = n.config("widget.lazyLoad", !1)),
                        (n._progressiveLoad = n.config("widget.progressiveLoad", !1)),
                        (n.templateId = n.config("widget.template")),
                        n.render(),
                        n.$el.appendTo(n.$container),
                        n.$container.addClass("crt-widget-carousel");
                    var o = n.config("widget.renderMode", "dynamic");
                    return (
                        (n.carousel = "static" === o ? new is(n, n.$el, n.$refs.stage, n.$refs.slider) : new as(n, n.$el, n.$refs.stage, n.$refs.slider)),
                        n.carousel.on(Fo.CAROUSEL_CHANGED, n.onCarouselChange.bind(n)),
                        n.feed.load({}).catch(function (t) {
                            throw (t.message ? n.showMessage(t.message, "error") : n.showMessage("Feed failed to load, check browser console for more info", "error"), t);
                        }),
                        n
                    );
                }
                return (
                    cs(e, t),
                    (e.prototype.reload = function () {
                        to.log("Carousel->reload"), (this.posts = []), (this.allLoaded = !1), this.carousel.reset(), this.feed.reset(), this.feed.load({});
                    }),
                    (e.prototype.loadMorePosts = function () {
                        to.log("Carousel->loadMorePosts"), this.feed.postCount > this.feed.numPosts() && this.feed.loadMore();
                    }),
                    (e.prototype.createPane = function (t) {
                        to.log("Carousel->createPane " + t);
                        var e = t;
                        t < 0 ? (e = this.feed.numPosts() + t) : t > this.feed.numPosts() - 1 && (e = t % this.feed.numPosts());
                        var n = new es("" + t),
                            o = this.feed.postAtIndex(e);
                        return n.addPost(this.createElement(o)), this._progressiveLoad && n.loadImage(), this._panesVisible.push(n), n;
                    }),
                    (e.prototype.onPostsLoaded = function (t, e) {
                        to.log("Carousel->onPostsLoaded " + e), 0 === t.length ? (this.allLoaded = !0) : this.carousel.setPanesLength(this.feed.numPosts());
                    }),
                    (e.prototype.onCarouselChange = function (t, e) {
                        (to.log("Carousel->onCarouselChange currentPane: " + e), this.config("widget.autoLoad")) && e >= this.feed.numPosts() - 2 * this.carousel.numVisiblePanes() && this.loadMorePosts();
                    }),
                    (e.prototype.onPrevClick = function () {
                        this.carousel.prev();
                    }),
                    (e.prototype.onNextClick = function () {
                        this.carousel.next();
                    }),
                    (e.prototype.onDebugClick = function () {
                        this.carousel.debug();
                    }),
                    (e.prototype.destroy = function () {
                        this.carousel.destroy(), this.$container.removeClass("crt-widget-carousel"), this.$container.removeClass("crt-carousel"), t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(Ri),
            ls = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            ds = (function (t) {
                function e(e) {
                    var n = t.call(this, e, Dn) || this;
                    return (
                        (n.posts = []),
                        (n.loading = !0),
                        (n.templateId = n.config("widget.template")),
                        n.render(),
                        n.$el.appendTo(n.$container),
                        n.$container.addClass("crt-widget-grid-carousel"),
                        (n.carousel = new as(n, n.$el, n.$refs.stage, n.$refs.slider)),
                        n.carousel.on(Fo.CAROUSEL_CHANGED, n.onCarouselChange.bind(n)),
                        n.feed.load({}).catch(function (t) {
                            throw (t.message ? n.showMessage(t.message, "error") : n.showMessage("Feed failed to load, check browser console for more info", "error"), t);
                        }),
                        n.createHandlers(),
                        n
                    );
                }
                return (
                    ls(e, t),
                    (e.prototype.createHandlers = function () {
                        var t = this;
                        (this.resize = Go(this.resize.bind(this), 100)),
                            (this.ro = new Lo.a(function (e) {
                                e.length > 0 && t.resize();
                            })),
                            this.ro.observe(this.$container[0]);
                    }),
                    (e.prototype.destroyHandlers = function () {
                        this.ro && this.ro.disconnect();
                    }),
                    (e.prototype.reload = function () {
                        to.log("GridCarousel->reload"), (this.posts = []), this.carousel.reset(), this.feed.reset(), this.feed.load({});
                    }),
                    (e.prototype.loadBefore = function () {
                        to.log("GridCarousel->loadBefore"), this.feed.loadBefore();
                    }),
                    (e.prototype.resize = function () {
                        this.trigger(Fo.WIDGET_RESIZE);
                    }),
                    (e.prototype.loadMorePosts = function () {
                        to.log("GridCarousel->loadMorePosts"), this.feed.postCount > this.feed.numPosts() && this.feed.loadMore();
                    }),
                    (e.prototype.createPane = function (t) {
                        for (var e = this.feed.numPosts(), n = new es("" + t), o = this.config("widget.rows"), r = 0; r < o; r++) {
                            var i = t * o + r;
                            i < 0 ? (i = e + i) : i > e - 1 && (i %= e);
                            var s = this.feed.postAtIndex(i);
                            s && n.addPost(this.createElement(s));
                        }
                        return n;
                    }),
                    (e.prototype.createPostElement = function (t) {
                        var e = new Vi(this, t);
                        return (
                            e.on(Fo.POST_CLICK, this.onPostClick.bind(this)),
                            e.on(Fo.POST_CLICK_READ_MORE, this.onPostClickReadMore.bind(this)),
                            e.on(Fo.POST_IMAGE_LOADED, this.onPostImageLoaded.bind(this)),
                            this.trigger(Fo.POST_CREATED, e),
                            e
                        );
                    }),
                    (e.prototype.createAdElement = function (t) {
                        var e = new Zi(this, t);
                        return this.trigger(Fo.AD_CREATED, e), e;
                    }),
                    (e.prototype.onPostsLoaded = function (t) {
                        if ((to.log("GridCarousel->onPostsLoaded"), (this.loading = !1), 0 !== t.length)) {
                            var e = this.config("widget.rows"),
                                n = Math.ceil(this.feed.numPosts() / e);
                            this.carousel.setPanesLength(n, !1, 0);
                        }
                    }),
                    (e.prototype.onCarouselChange = function (t, e, n) {
                        if ((to.log("GridCarousel->onCarouselChange currentPane: " + n), this.config("widget.autoLoad"))) {
                            var o = this.feed.numPosts() - 2 * this.carousel.PANES_VISIBLE;
                            n * this.config("widget.rows") >= o && this.loadMorePosts();
                        }
                    }),
                    (e.prototype.onPrevClick = function () {
                        this.carousel.prev();
                    }),
                    (e.prototype.onNextClick = function () {
                        this.carousel.next();
                    }),
                    (e.prototype.destroy = function () {
                        this.destroyHandlers(), this.carousel.destroy(), this.$container.removeClass("crt-widget-grid-carousel"), t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(qi),
            ps = (function () {
                function t(t) {
                    (this.autoLoading = !0), (this.onAutoLoadFire = t);
                }
                return (
                    (t.prototype.start = function () {
                        to.log("AutoloadWorker->start"), (this.autoLoading = !0), this.setTimeout();
                    }),
                    (t.prototype.setTimeout = function () {
                        this.autoLoadTimeout = window.setTimeout(this._onAutoLoadFire.bind(this), 3e4);
                    }),
                    (t.prototype.stop = function () {
                        (this.autoLoading = !1), window.clearTimeout(this.autoLoadTimeout);
                    }),
                    (t.prototype._onAutoLoadFire = function () {
                        this.onAutoLoadFire(), this.setTimeout();
                    }),
                    (t.prototype.destroy = function () {
                        this.autoLoading && this.stop();
                    }),
                    t
                );
            })(),
            hs = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            fs = (function (t) {
                function e(e) {
                    var n = t.call(this, e, Ln) || this;
                    return (
                        (n.allLoaded = !1),
                        (n.allLoaded = !1),
                        (n._config.feed.limit = 50),
                        n.renderWidget(),
                        n.$el.appendTo(n.$container),
                        n.$container.addClass("crt-widget-carousel"),
                        n.$container.addClass("crt-widget-panel"),
                        n.config("post.fixedHeight") && n.$container.addClass("crt-panel-fixed-height"),
                        (n.carousel = new as(n, n.$el, n.$refs.stage, n.$refs.slider)),
                        n.carousel.on(Fo.CAROUSEL_CHANGED, n.onCarouselChange.bind(n)),
                        n.feed.load({}).catch(function (t) {
                            throw (t.message ? n.showMessage(t.message, "error") : n.showMessage("Feed failed to load, check browser console for more info", "error"), t);
                        }),
                        n.config("widget.autoLoadNew") &&
                            ((n.autoLoadWorker = new ps(function () {
                                n.feed.loadBefore();
                            })),
                            n.autoLoadWorker.start()),
                        n
                    );
                }
                return (
                    hs(e, t),
                    (e.prototype.reload = function () {
                        this.feed.reset(), this.feed.load({}), this.reset();
                    }),
                    (e.prototype.reset = function () {
                        var t = this.carousel.currentPane();
                        this.carousel.setPanesLength(this.feed.numPosts(), !0, t);
                    }),
                    (e.prototype.loadMorePosts = function () {
                        this.log("loadMorePosts"), this.feed.postCount > this.feed.numPosts() && this.feed.loadMore();
                    }),
                    (e.prototype.createPane = function (t) {
                        this.log("createPane " + t);
                        var e = t;
                        t < 0 ? (e = this.feed.numPosts() + t) : t > this.feed.numPosts() - 1 && (e = t % this.feed.numPosts());
                        var n = new es("" + t),
                            o = this.feed.postAtIndex(e);
                        return n.addPost(this.createElement(o)), n;
                    }),
                    (e.prototype.onPostsLoaded = function (t, e) {
                        if ((this.log("onPostsLoaded"), 0 === t.length)) this.allLoaded = !0;
                        else if ("before" === e) {
                            var n = this.carousel.currentPane();
                            (n += t.length), this.carousel.setPanesLength(this.feed.numPosts(), !0, n);
                        } else this.carousel.setPanesLength(this.feed.numPosts());
                    }),
                    (e.prototype.onCarouselChange = function (t, e) {
                        (this.log("onCarouselChange currentPane: " + e), this.config("widget.autoLoad")) && e >= this.feed.numPosts() - 2 * this.carousel.PANES_VISIBLE && this.loadMorePosts();
                    }),
                    (e.prototype.onPrevClick = function () {
                        this.carousel.prev();
                    }),
                    (e.prototype.onNextClick = function () {
                        this.carousel.next();
                    }),
                    (e.prototype.log = function (t) {
                        to.log("Panel->" + t);
                    }),
                    (e.prototype.destroy = function () {
                        this.autoLoadWorker && this.autoLoadWorker.destroy(), this.carousel.destroy(), this.$container.removeClass("crt-widget-carousel"), this.$container.removeClass("crt-widget-panel"), t.prototype.destroy.call(this);
                    }),
                    e
                );
            })(Hi),
            gs = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            vs = (function (t) {
                function e(e, n) {
                    var o = t.call(this, e, n) || this;
                    o.render(),
                        o.$refs.image.css({ opacity: 0 }),
                        o.json.image
                            ? (o.$refs.image[0].addEventListener("load", o.onImageLoaded.bind(o), !0), o.$refs.image[0].addEventListener("error", o.onImageError.bind(o), !0))
                            : window.setTimeout(function () {
                                  o.setHeight();
                              }, 100),
                        0 !== o.json.url.indexOf("http") && o.$el.find(".crt-post-share").hide();
                    var r = o.widget.config("post.imageWidth", 250);
                    return o.$refs.imageCol.css("width", r), o.setupVideo(), o.setupCarousel(), o.setupShare(), o.setupCommentsLikes(), o;
                }
                return (
                    gs(e, t),
                    (e.prototype.onImageLoaded = function () {
                        this.$refs.image.animate({ opacity: 1 }), this.setHeight(), this.trigger(Fo.POST_IMAGE_LOADED, this), this.widget.trigger(Fo.POST_IMAGE_LOADED, this);
                    }),
                    (e.prototype.onImageError = function () {
                        this.$refs.image.hide(), this.setHeight(), this.trigger(Fo.POST_IMAGE_FAILED, this), this.widget.trigger(Fo.POST_IMAGE_FAILED, this);
                    }),
                    (e.prototype.setHeight = function () {
                        this.layout();
                    }),
                    (e.prototype.getHeight = function () {
                        return this.$el.hasClass("crt-post-max-height") ? this.$refs.postC.height() : this.$el.find(".crt-post-content").height() + this.$el.find(".crt-post-footer").height() + 2;
                    }),
                    (e.prototype.layout = function () {
                        this.layoutFooter(), this.trigger(Fo.POST_LAYOUT_CHANGED, this);
                    }),
                    (e.prototype.layoutFooter = function () {
                        var t = this.$el.find(".crt-post-username"),
                            e = this.$el.find(".crt-date"),
                            n = this.$el.find(".crt-post-footer"),
                            o = this.$el.find(".crt-post-share"),
                            r = this.$el.find(".crt-post-userimage"),
                            i = n.width();    
                        t.width() + e.width() + o.width() + r.width() + 40 > i && t.hide();
                    }),
                    e
                );
            })(ir),
            ms = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            ys = {
                Waterfall: Gi,
                Grid: Qi,
                Carousel: us,
                GridCarousel: ds,
                Panel: fs,
                List: (function (t) {
                    function e(e) {
                        var n = t.call(this, e, xn) || this;
                        return (
                            (n.loading = !1),
                            (n.posts = []),
                            (n.postElements = []),
                            (n.rowsMax = 0),
                            (n.templateId = n.config("widget.template")),
                            n.render(),
                            n.$container.append(n.$el),
                            (n.$scroller = oo(window)),
                            n.$el.addClass("crt-widget-list"),
                            n.$refs.loadMore.hide(),
                            n.createHandlers(),
                            n.load(),
                            n
                        );
                    }
                    return (
                        ms(e, t),
                        (e.prototype.load = function () {
                            var t = this;
                            if ((this.log("load " + this.feed.loading), !this.feed.loading)) {
                                var e = this.config("widget.postsPerPage", 12);
                                this.feed
                                    .afterIterator(e)
                                    .each(function (e, n) {
                                        var o = t.createElement(e);
                                        t.postElements.push(o), t.$refs.feed.append(o.$el), o.layout(), t.config("widget.animate") && o.fadeIn(n);
                                    })
                                    .done(function () {
                                        t.config("widget.showLoadMore") ? (t.feed.allPostsLoaded ? t.$refs.loadMore.hide() : t.$refs.loadMore.show()) : t.$refs.loadMore.hide();
                                    })
                                    .catch(function (e) {
                                        throw (e.message ? t.showMessage(e.message, "error") : t.showMessage("Feed failed to load, check browser console for more info", "error"), e);
                                    });
                            }
                        }),
                        (e.prototype.reload = function () {
                            this.feed.reset(), this.$refs.feed.empty(), this.load();
                        }),
                        (e.prototype.createHandlers = function () {
                            var t = this,
                                e = this.id;
                            (this.updateLayout = Go(this.updateLayout.bind(this))),
                                (this.checkScroll = Go(this.checkScroll.bind(this))),
                                oo(window).on("curatorCssLoaded." + e, this.updateLayout),
                                oo(document).on("ready." + e, this.updateLayout),
                                this.config("widget.continuousScroll") && oo(window).on("scroll." + e, this.checkScroll),
                                (this.ro = new Lo.a(function (e) {
                                    e.length > 0 && t.updateLayout();
                                })),
                                this.ro.observe(this.$el[0]);
                        }),
                        (e.prototype.destroyHandlers = function () {
                            var t = this.id;
                            oo(window).off("curatorCssLoaded." + t), oo(document).off("ready." + t), oo(window).off("scroll." + t), this.ro && this.ro.disconnect();
                        }),
                        (e.prototype.updateLayout = function () {}),
                        (e.prototype.checkScroll = function () {
                            this.log("checkScroll");
                            var t = this.$el.offset().top + this.$refs.feedWindow.height();
                            if (this.$scroller.scrollTop() + oo(window).height() - t > this.config("widget.continuousScrollOffset") && !this.feed.loading && !this.feed.allPostsLoaded) {
                                var e = this.config("widget.loadMoreRows");
                                (this.rowsMax += e), this.updateLayout();
                            }
                        }),
                        (e.prototype.onPostsLoaded = function (t) {
                            this.log("onPostsLoaded"), t.length;
                        }),
                        (e.prototype.onMoreClick = function () {
                            this.load();
                        }),
                        (e.prototype.createPostElement = function (t) {
                            var e = new vs(this, t);
                            return (
                                e.on(Fo.POST_CLICK, this.onPostClick.bind(this)),
                                e.on(Fo.POST_CLICK_READ_MORE, this.onPostClickReadMore.bind(this)),
                                e.on(Fo.POST_IMAGE_LOADED, this.onPostImageLoaded.bind(this)),
                                this.trigger(Fo.POST_CREATED, e),
                                e
                            );
                        }),
                        (e.prototype.log = function (t) {
                            var e = "List[" + this.id + "]";
                            to.log(e + "->" + t);
                        }),
                        (e.prototype.destroy = function () {
                            this.destroyHandlers(), this.$el.empty().removeClass("crt-widget-list").css({ height: "", overflow: "" }), t.prototype.destroy.call(this);
                        }),
                        (e.prototype.setStyles = function (t) {
                            this.sheet ? this.clearStyles() : (this.sheet = io.createSheet()), this.setStylesGeneral(t);
                        }),
                        e
                    );
                })(Ri),
            };
        function ws(t, e, n) {
            var o = (function (t) {
                var e = "",
                    n = t.container;
                t.feed && t.feed.id ? (e = t.feed.id) : t.feedId && (e = t.feedId);
                var o = oo('[data-crt-feed-id="' + e + '"]');
                return (o.length > 0 || (o = oo(".crt-feed-" + e)).length > 0 || (o = oo("#curator-" + e)).length > 0 || (o = oo(n)).length > 0) && o[0];
            })(t);
            if (o) {
                t.container = o;
                var r = void 0;
                switch (t.type) {
                    case "waterfall":
                        r = ys.Waterfall;
                        break;
                    case "Grid":
                        r = ys.Grid;
                        break;
                    case "Carousel":
                        r = ys.Carousel;
                        break;
                    case "GridCarousel":
                        r = ys.GridCarousel;
                        break;
                    case "Panel":
                        r = ys.Panel;
                        break;
                    case "List":
                        r = ys.List;
                        break;
                    default:
                        r = ys.Waterfall;
                }
                var i = new r(t);
                return e && (n = Wn.themeStyles(t.type, t.theme, e)), n && i.setStyles(n), i;
            }
            to.error("Curator - could not find container");
        }
        var _s = (function () {
                var t = function (e, n) {
                    return (t =
                        Object.setPrototypeOf ||
                        ({ __proto__: [] } instanceof Array &&
                            function (t, e) {
                                t.__proto__ = e;
                            }) ||
                        function (t, e) {
                            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                        })(e, n);
                };
                return function (e, n) {
                    function o() {
                        this.constructor = e;
                    }
                    t(e, n), (e.prototype = null === n ? Object.create(n) : ((o.prototype = n.prototype), new o()));
                };
            })(),
            bs = function () {
                return (bs =
                    Object.assign ||
                    function (t) {
                        for (var e, n = 1, o = arguments.length; n < o; n++) for (var r in (e = arguments[n])) Object.prototype.hasOwnProperty.call(e, r) && (t[r] = e[r]);
                        return t;
                    }).apply(this, arguments);
            },
            As = {
                Post: ir,
                Control: er,
                DevPanel: (function (t) {
                    function e() {
                        var e = t.call(this) || this;
                        (e.config = {}),
                            (e.templateId =
                                '\n<div class="example-panel">\n    <a class="panel-close" c-on:click="onCloseClick">-</a>\n    <div class="example-panel-body">\n        <table>\n        <tr>\n            <td>Background</td>\n            <td>\n            <a href="#" class="bg" c-on:click="onBGClick" data-bg="#000000">Black</a> |\n            <a href="#" class="bg" c-on:click="onBGClick" data-bg="#FFFFFF">White</a> |\n            <a href="#" class="bg" c-on:click="onBGClick" data-bg="#EFEFEF">Grey BG</a>\n        </td>\n        </tr>\n        <tr>\n            <td>-</td>\n        </tr>\n        <tr>\n            <td>Widget: </td>\n            <td><a href="#" class="create" c-on:click="createWidget">Create</a> |\n                <a href="#" class="destroy" c-on:click="destroyWidget">Destroy</a> |\n                <a href="#" class="destroy" c-on:click="resetWidget">Reset</a></td>\n        </tr>\n        <tr>\n            <td>Feed ID</td>\n            <td><input id="feedId" c-on:blur="onFeedBlur"></td>\n                </tr>\n                <tr>\n                <td>API</td>\n                <td>\n                <select id="api" c-on:change="onApiChange">\n                <option>https://api.curator.io</option>\n            <option>http://curator-api.local</option>\n            <option>http://curator-api.test</option>\n            </select>\n            </td>\n        </tr>\n        <tr>\n            <td>Type: </td>\n            <td>\n            <select id="type" c-on:change="onTypeChange">\n                <option>Waterfall</option>\n                <option>Carousel</option>\n                <option>Grid</option>\n                <option>GridCarousel</option>\n                <option>Panel</option>\n                <option>List</option>\n                </select>\n                </td>\n                </tr>\n                <tr>\n                <td>Theme: </td>\n            <td>\n            <select id="theme" c-on:change="onThemeChange">\n                </select>\n                </td>\n                </tr>\n                <tr id="tr-options">\n                <td>Options: </td>\n            <td><textarea id="options" c-on:blur="onOptionsBlur"></textarea></td>\n        </tr>\n        <tr id="tr-styles">\n            <td>Styles: </td>\n            <td><textarea id="styles" c-on:blur="onStylesBlur"></textarea></td>\n        </tr>\n        </table>\n    </div>\n</div>'),
                            e.render(),
                            oo("body").append(e.$el);
                        try {
                            var n = window.localStorage.getItem("config");
                            n && (e.config = JSON.parse(n));
                        } catch (t) {
                            window.console.log("ERROR IN JSON: config"), window.console.log(t);
                        }
                        try {
                            var o = window.localStorage.getItem("styles");
                            o && (e.styles = JSON.parse(o));
                        } catch (t) {
                            window.console.log("ERROR IN JSON: styles"), window.console.log(t);
                        }
                        e.config || (e.config = { feed: {} });
                        var r = "0DE3D84D-E173-4522-8B21-EE9AF2D7";
                        if (window.URLSearchParams) {
                            var i = new window.URLSearchParams(window.location.search);
                            r = i.get("feed_id") ? i.get("feed_id") : r;
                        }
                        return (
                            e.config.feed || (e.config.feed = {}),
                            e.config.feed.apiEndpoint || (e.config.feed.apiEndpoint = "https://api.curator.io"),
                            e.config.feed.id || (e.config.feed.id = r),
                            e.config.type || (e.config.type = "Waterfall"),
                            e.config.widgetTheme || (e.config.widgetTheme = "sydney"),
                            e.updateThemesOptions(e.config.type),
                            e.updateOptions(),
                            e.updateStyles(),
                            e.createWidget(),
                            oo("#tr-styles").hide(),
                            e
                        );
                    }
                    return (
                        _s(e, t),
                        (e.prototype.createConfig = function (t, e, n) {
                            void 0 === n && (n = !1),
                                (t !== this.config.type || n) &&
                                    ((this.config = Wn.typeConfig(t)), this.config.feed || (this.config.feed = {}), (this.config.feed.id = oo("#feedId").val()), (this.config.feed.apiEndpoint = oo("#api").val())),
                                (this.config.type = t),
                                (this.config.theme = e);
                            var o = Wn.themeConfig(t, e),
                                r = Wn.themeStyles(t, e, {});
                            (this.config = bs(bs({}, this.config), o)), (this.styles = r);
                        }),
                        (e.prototype.onApiChange = function (t) {
                            t.target && ((this.config.feed.apiEndpoint = oo(t.target).val()), this.updateOptions(), this.createWidget());
                        }),
                        (e.prototype.onTypeChange = function (t) {
                            if (t.target) {
                                var e = oo(t.target).val(),
                                    n = Wn.defaultTheme(e);
                                this.updateThemesOptions(e), this.createConfig(e, n), this.updateOptions(), this.updateStyles(), this.createWidget();
                            }
                        }),
                        (e.prototype.onThemeChange = function (t) {
                            if (t.target) {
                                var e = oo(t.target).val();
                                this.createConfig(this.config.type, e), this.updateOptions(), this.updateStyles(), this.createWidget();
                            }
                        }),
                        (e.prototype.onFeedBlur = function (t) {
                            t.target && ((this.config.feed.id = oo(t.target).val()), this.updateOptions(), this.createWidget());
                        }),
                        (e.prototype.onOptionsBlur = function (t) {
                            if (t.target) {
                                var e = oo("#options").val();
                                try {
                                    (this.config = JSON.parse(e)), window.localStorage.setItem("config", e), this.createWidget();
                                } catch (t) {
                                    console.log("ERROR!!!!"), console.log(t);
                                }
                            }
                        }),
                        (e.prototype.onStylesBlur = function (t) {
                            if (t.target) {
                                var e = "{}";
                                try {
                                    (e = oo("#styles").val()), console.log(e), (this.styles = JSON.parse(e));
                                } catch (t) {
                                    console.log("ERROR!!!!"), console.log(t.message), (this.styles = {});
                                }
                                try {
                                    window.localStorage.setItem("styles", e);
                                } catch (t) {
                                    console.log(t);
                                }
                                this.createWidget();
                            }
                        }),
                        (e.prototype.onBGClick = function (t) {
                            t.target && oo(t.target).data("bg") && oo("body").css("background-color");
                        }),
                        (e.prototype.onCloseClick = function (t) {
                            t.target && this.$el.toggleClass("hidden");
                        }),
                        (e.prototype.destroyWidget = function () {
                            this.widget && this.widget.destroy(), (this.widget = void 0);
                        }),
                        (e.prototype.createWidget = function () {
                            this.destroyWidget();
                            var t = bs({}, this.config);
                            (t.container = "#curator-feed"), (this.widget = ws(t)), this.widget && this.styles && this.widget.setStyles(this.styles);
                        }),
                        (e.prototype.resetWidget = function () {
                            this.destroyWidget(), this.createConfig(this.config.type, this.config.widgetTheme, !0), this.createWidget();
                        }),
                        (e.prototype.updateThemesOptions = function (t) {
                            oo("#theme").empty();
                            var e = Wn.widgetThemes(t);
                            if (e)
                                for (var n in e) {
                                    var o = e[n];
                                    oo("#theme").append("<option>" + o + "</option>");
                                }
                        }),
                        (e.prototype.updateOptions = function () {
                            oo("#type").val(this.config.type), oo("#theme").val(this.config.theme), oo("#feedId").val(this.config.feed.id), oo("#api").val(this.config.feed.apiEndpoint);
                            var t = JSON.stringify(this.config);
                            try {
                                window.localStorage.setItem("config", t);
                            } catch (t) {
                                console.log(t);
                            }
                            var e = this.prettifyJSON(t);
                            oo("#options").val(e);
                        }),
                        (e.prototype.updateStyles = function () {
                            var t = JSON.stringify(this.styles);
                            try {
                                window.localStorage.setItem("styles", t);
                            } catch (t) {
                                console.log(t);
                            }
                            var e = this.prettifyJSON(t);
                            oo("#styles").val(e);
                        }),
                        (e.prototype.prettifyJSON = function (t) {
                            return t
                                ? (t = (t = (t = (t = (t = (t = (t = t.replace(/([e0-9"]),/g, "$1,\n")).replace(/}/g, "\n}")).replace(/{/g, "{\n")).replace(/},/g, "},\n")).replace(/},/g, "},\n")).replace(/{\n\n},/g, "{},")).replace(
                                      /},\n\n/g,
                                      "},\n"
                                  ))
                                : "";
                        }),
                        e
                    );
                })(er),
            };
        function Cs(t, e, n) {
            return Ao.t(t, e, n);
        }
        function Ps() {
            console.log("depreciated");
        }
        var Es = { Html: io, String: Yn };
    },
]);
