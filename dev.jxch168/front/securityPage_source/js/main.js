/*! fancyBox v2.1.5 fancyapps.com | fancyapps.com/fancybox/#license */
(function(a, x, i, u) {
    var k = i("html"), g = i(a), e = i(x), j = i.fancybox = function() { j.open.apply(this, arguments) }, o = navigator.userAgent.match(/msie/i), d = null, A = x.createTouch !== u, y = function(b) { return b && b.hasOwnProperty && b instanceof i }, c = function(b) { return b && "string" === i.type(b) }, z = function(b) { return c(b) && 0 < b.indexOf("%") },
        h = function(b, l) {
            var f = parseInt(b, 10) || 0;
            l && z(b) && (f *= j.getViewport()[l] / 100);
            return Math.ceil(f);
        },
        m = function(l, f) { return h(l, f) + "px" };
    i.extend(j, {
        version: "2.1.5", defaults: { padding: 15, margin: 20, width: 800, height: 600, minWidth: 100, minHeight: 100, maxWidth: 9999, maxHeight: 9999, pixelRatio: 1, autoSize: !0, autoHeight: !1, autoWidth: !1, autoResize: !0, autoCenter: !A, fitToView: !0, aspectRatio: !1, topRatio: 0.5, leftRatio: 0.5, scrolling: "auto", wrapCSS: "", arrows: !0, closeBtn: !0, closeClick: !1, nextClick: !1, mouseWheel: !0, autoPlay: !1, playSpeed: 3000, preload: 3, modal: !1, loop: !0, ajax: { dataType: "html", headers: { "X-fancyBox": !0 } }, iframe: { scrolling: "auto", preload: !0 }, swf: { wmode: "transparent", allowfullscreen: "true", allowscriptaccess: "always" }, keys: { next: { 13: "left", 34: "up", 39: "left", 40: "up" }, prev: { 8: "right", 33: "down", 37: "right", 38: "down" }, close: [27], play: [32], toggle: [70] }, direction: { next: "left", prev: "right" }, scrollOutside: !0, index: 0, type: null, href: null, content: null, title: null, tpl: { wrap: "<div class=\"fancybox-wrap\" tabIndex=\"-1\"><div class=\"fancybox-skin\"><div class=\"fancybox-outer\"><div class=\"fancybox-inner\"></div></div></div></div>", image: "<img class=\"fancybox-image\" src=\"{href}\" alt=\"\" />", iframe: "<iframe id=\"fancybox-frame{rnd}\" name=\"fancybox-frame{rnd}\" class=\"fancybox-iframe\" frameborder=\"0\" vspace=\"0\" hspace=\"0\" webkitAllowFullScreen mozallowfullscreen allowFullScreen" + (o ? " allowtransparency=\"true\"" : "") + "></iframe>", error: "<p class=\"fancybox-error\">The requested content cannot be loaded.<br/>Please try again later.</p>", closeBtn: "<a title=\"Close\" class=\"fancybox-item fancybox-close\" href=\"javascript:;\"></a>", next: "<a title=\"Next\" class=\"fancybox-nav fancybox-next\" href=\"javascript:;\"><span></span></a>", prev: "<a title=\"Previous\" class=\"fancybox-nav fancybox-prev\" href=\"javascript:;\"><span></span></a>" }, openEffect: "fade", openSpeed: 250, openEasing: "swing", openOpacity: !0, openMethod: "zoomIn", closeEffect: "fade", closeSpeed: 250, closeEasing: "swing", closeOpacity: !0, closeMethod: "zoomOut", nextEffect: "elastic", nextSpeed: 250, nextEasing: "swing", nextMethod: "changeIn", prevEffect: "elastic", prevSpeed: 250, prevEasing: "swing", prevMethod: "changeOut", helpers: { overlay: !0, title: !0 }, onCancel: i.noop, beforeLoad: i.noop, afterLoad: i.noop, beforeShow: i.noop, afterShow: i.noop, beforeChange: i.noop, beforeClose: i.noop, afterClose: i.noop }, group: {}, opts: {}, previous: null, coming: null, current: null, isActive: !1, isOpen: !1, isOpened: !1, wrap: null, skin: null, outer: null, inner: null, player: { timer: null, isActive: !1 }, ajaxLoad: null, imgPreload: null, transitions: {}, helpers: {},
        open: function(b, f) {
            if (b && (i.isPlainObject(f) || (f = {}), !1 !== j.close(!0))) {
                return i.isArray(b) || (b = y(b) ? i(b).get() : [b]), i.each(b, function(v, w) {
                    var q = {}, t, s, r, n, p;
                    "object" === i.type(w) && (w.nodeType && (w = i(w)), y(w) ? (q = { href: w.data("fancybox-href") || w.attr("href"), title: w.data("fancybox-title") || w.attr("title"), isDom: !0, element: w }, i.metadata && i.extend(!0, q, w.metadata())) : q = w);
                    t = f.href || q.href || (c(w) ? w : null);
                    s = f.title !== u ? f.title : q.title || "";
                    n = (r = f.content || q.content) ? "html" : f.type || q.type;
                    !n && q.isDom && (n = w.data("fancybox-type"), n || (n = (n = w.prop("class").match(/fancybox\.(\w+)/)) ? n[1] : null));
                    c(t) && (n || (j.isImage(t) ? n = "image" : j.isSWF(t) ? n = "swf" : "#" === t.charAt(0) ? n = "inline" : c(w) && (n = "html", r = w)), "ajax" === n && (p = t.split(/\s+/, 2), t = p.shift(), p = p.shift()));
                    r || ("inline" === n ? t ? r = i(c(t) ? t.replace(/.*(?=#[^\s]+$)/, "") : t) : q.isDom && (r = w) : "html" === n ? r = t : !n && (!t && q.isDom) && (n = "inline", r = w));
                    i.extend(q, { href: t, type: n, content: r, title: s, selector: p });
                    b[v] = q;
                }), j.opts = i.extend(!0, {}, j.defaults, f), f.keys !== u && (j.opts.keys = f.keys ? i.extend({}, j.defaults.keys, f.keys) : !1), j.group = b, j._start(j.opts.index);
            }
        },
        cancel: function() {
            var b = j.coming;
            b && !1 !== j.trigger("onCancel") && (j.hideLoading(), j.ajaxLoad && j.ajaxLoad.abort(), j.ajaxLoad = null, j.imgPreload && (j.imgPreload.onload = j.imgPreload.onerror = null), b.wrap && b.wrap.stop(!0, !0).trigger("onReset").remove(), j.coming = null, j.current || j._afterZoomOut(b));
        },
        close: function(b) {
            j.cancel();
            !1 !== j.trigger("beforeClose") && (j.unbindEvents(), j.isActive && (!j.isOpen || !0 === b ? (i(".fancybox-wrap").stop(!0).trigger("onReset").remove(), j._afterZoomOut()) : (j.isOpen = j.isOpened = !1, j.isClosing = !0, i(".fancybox-item, .fancybox-nav").remove(), j.wrap.stop(!0, !0).removeClass("fancybox-opened"), j.transitions[j.current.closeMethod]())));
        },
        play: function(b) {
            var l = function() { clearTimeout(j.player.timer) },
                f = function() {
                    l();
                    j.current && j.player.isActive && (j.player.timer = setTimeout(j.next, j.current.playSpeed));
                },
                n = function() {
                    l();
                    e.unbind(".player");
                    j.player.isActive = !1;
                    j.trigger("onPlayEnd");
                };
            if (!0 === b || !j.player.isActive && !1 !== b) {
                if (j.current && (j.current.loop || j.current.index < j.group.length - 1)) {
                    j.player.isActive = !0, e.bind({ "onCancel.player beforeClose.player": n, "onUpdate.player": f, "beforeLoad.player": l }), f(), j.trigger("onPlayStart");
                }
            } else {
                n();
            }
        },
        next: function(b) {
            var f = j.current;
            f && (c(b) || (b = f.direction.next), j.jumpto(f.index + 1, b, "next"));
        },
        prev: function(b) {
            var f = j.current;
            f && (c(b) || (b = f.direction.prev), j.jumpto(f.index - 1, b, "prev"));
        },
        jumpto: function(b, l, f) {
            var n = j.current;
            n && (b = h(b), j.direction = l || n.direction[b >= n.index ? "next" : "prev"], j.router = f || "jumpto", n.loop && (0 > b && (b = n.group.length + b % n.group.length), b %= n.group.length), n.group[b] !== u && (j.cancel(), j._start(b)));
        },
        reposition: function(b, n) {
            var l = j.current, p = l ? l.wrap : null, f;
            p && (f = j._getPosition(n), b && "scroll" === b.type ? (delete f.position, p.stop(!0, !0).animate(f, 200)) : (p.css(f), l.pos = i.extend({}, l.dim, f)));
        },
        update: function(b) {
            var l = b && b.type, f = !l || "orientationchange" === l;
            f && (clearTimeout(d), d = null);
            j.isOpen && !d && (d = setTimeout(function() {
                var n = j.current;
                n && !j.isClosing && (j.wrap.removeClass("fancybox-tmp"), (f || "load" === l || "resize" === l && n.autoResize) && j._setDimension(), "scroll" === l && n.canShrink || j.reposition(b), j.trigger("onUpdate"), d = null);
            }, f && !A ? 0 : 300));
        },
        toggle: function(b) { j.isOpen && (j.current.fitToView = "boolean" === i.type(b) ? b : !j.current.fitToView, A && (j.wrap.removeAttr("style").addClass("fancybox-tmp"), j.trigger("onUpdate")), j.update()) },
        hideLoading: function() {
            e.unbind(".loading");
            i("#fancybox-loading").remove();
        },
        showLoading: function() {
            var b, f;
            j.hideLoading();
            b = i("<div id=\"fancybox-loading\"><div></div></div>").click(j.cancel).appendTo("body");
            e.bind("keydown.loading", function(l) {
                if (27 === (l.which || l.keyCode)) {
                    l.preventDefault(), j.cancel();
                }
            });
            j.defaults.fixed || (f = j.getViewport(), b.css({ position: "absolute", top: 0.5 * f.h + f.y, left: 0.5 * f.w + f.x }));
        },
        getViewport: function() {
            var b = j.current && j.current.locked || !1, f = { x: g.scrollLeft(), y: g.scrollTop() };
            b ? (f.w = b[0].clientWidth, f.h = b[0].clientHeight) : (f.w = A && a.innerWidth ? a.innerWidth : g.width(), f.h = A && a.innerHeight ? a.innerHeight : g.height());
            return f;
        },
        unbindEvents: function() {
            j.wrap && y(j.wrap) && j.wrap.unbind(".fb");
            e.unbind(".fb");
            g.unbind(".fb");
        },
        bindEvents: function() {
            var b = j.current, f;
            b && (g.bind("orientationchange.fb" + (A ? "" : " resize.fb") + (b.autoCenter && !b.locked ? " scroll.fb" : ""), j.update), (f = b.keys) && e.bind("keydown.fb", function(n) {
                var p = n.which || n.keyCode, l = n.target || n.srcElement;
                if (27 === p && j.coming) {
                    return !1;
                }
                !n.ctrlKey && (!n.altKey && !n.shiftKey && !n.metaKey && (!l || !l.type && !i(l).is("[contenteditable]"))) && i.each(f, function(r, q) {
                    if (1 < b.group.length && q[p] !== u) {
                        return j[r](q[p]), n.preventDefault(), !1;
                    }
                    if (-1 < i.inArray(p, q)) {
                        return j[r](), n.preventDefault(), !1;
                    }
                });
            }), i.fn.mousewheel && b.mouseWheel && j.wrap.bind("mousewheel.fb", function(r, s, l, q) {
                for (var p = i(r.target || null), n = !1; p.length && !n && !p.is(".fancybox-skin") && !p.is(".fancybox-wrap");) {
                    n = p[0] && !(p[0].style.overflow && "hidden" === p[0].style.overflow) && (p[0].clientWidth && p[0].scrollWidth > p[0].clientWidth || p[0].clientHeight && p[0].scrollHeight > p[0].clientHeight), p = i(p).parent();
                }
                if (0 !== s && !n && 1 < j.group.length && !b.canShrink) {
                    if (0 < q || 0 < l) {
                        j.prev(0 < q ? "down" : "left");
                    } else {
                        if (0 > q || 0 > l) {
                            j.next(0 > q ? "up" : "right");
                        }
                    }
                    r.preventDefault();
                }
            }));
        },
        trigger: function(b, l) {
            var f, n = l || j.coming || j.current;
            if (n) {
                i.isFunction(n[b]) && (f = n[b].apply(n, Array.prototype.slice.call(arguments, 1)));
                if (!1 === f) {
                    return !1;
                }
                n.helpers && i.each(n.helpers, function(q, p) {
                    if (p && j.helpers[q] && i.isFunction(j.helpers[q][b])) {
                        j.helpers[q][b](i.extend(!0, {}, j.helpers[q].defaults, p), n);
                    }
                });
                e.trigger(b);
            }
        },
        isImage: function(b) { return c(b) && b.match(/(^data:image\/.*,)|(\.(jp(e|g|eg)|gif|png|bmp|webp|svg)((\?|#).*)?$)/i) },
        isSWF: function(b) { return c(b) && b.match(/\.(swf)((\?|#).*)?$/i) },
        _start: function(b) {
            var l = {}, f, n;
            b = h(b);
            f = j.group[b] || null;
            if (!f) {
                return !1;
            }
            l = i.extend(!0, {}, j.opts, f);
            f = l.margin;
            n = l.padding;
            "number" === i.type(f) && (l.margin = [f, f, f, f]);
            "number" === i.type(n) && (l.padding = [n, n, n, n]);
            l.modal && i.extend(!0, l, { closeBtn: !1, closeClick: !1, nextClick: !1, arrows: !1, mouseWheel: !1, keys: null, helpers: { overlay: { closeClick: !1 } } });
            l.autoSize && (l.autoWidth = l.autoHeight = !0);
            "auto" === l.width && (l.autoWidth = !0);
            "auto" === l.height && (l.autoHeight = !0);
            l.group = j.group;
            l.index = b;
            j.coming = l;
            if (!1 === j.trigger("beforeLoad")) {
                j.coming = null;
            } else {
                n = l.type;
                f = l.href;
                if (!n) {
                    return j.coming = null, j.current && j.router && "jumpto" !== j.router ? (j.current.index = b, j[j.router](j.direction)) : !1;
                }
                j.isActive = !0;
                if ("image" === n || "swf" === n) {
                    l.autoHeight = l.autoWidth = !1, l.scrolling = "visible";
                }
                "image" === n && (l.aspectRatio = !0);
                "iframe" === n && A && (l.scrolling = "scroll");
                l.wrap = i(l.tpl.wrap).addClass("fancybox-" + (A ? "mobile" : "desktop") + " fancybox-type-" + n + " fancybox-tmp " + l.wrapCSS).appendTo(l.parent || "body");
                i.extend(l, { skin: i(".fancybox-skin", l.wrap), outer: i(".fancybox-outer", l.wrap), inner: i(".fancybox-inner", l.wrap) });
                i.each(["Top", "Right", "Bottom", "Left"], function(q, p) { l.skin.css("padding" + p, m(l.padding[q])) });
                j.trigger("onReady");
                if ("inline" === n || "html" === n) {
                    if (!l.content || !l.content.length) {
                        return j._error("content");
                    }
                } else {
                    if (!f) {
                        return j._error("href");
                    }
                }
                "image" === n ? j._loadImage() : "ajax" === n ? j._loadAjax() : "iframe" === n ? j._loadIframe() : j._afterLoad();
            }
        },
        _error: function(b) {
            i.extend(j.coming, { type: "html", autoWidth: !0, autoHeight: !0, minWidth: 0, minHeight: 0, scrolling: "no", hasError: b, content: j.coming.tpl.error });
            j._afterLoad();
        },
        _loadImage: function() {
            var b = j.imgPreload = new Image;
            b.onload = function() {
                this.onload = this.onerror = null;
                j.coming.width = this.width / j.opts.pixelRatio;
                j.coming.height = this.height / j.opts.pixelRatio;
                j._afterLoad();
            };
            b.onerror = function() {
                this.onload = this.onerror = null;
                j._error("image");
            };
            b.src = j.coming.href;
            !0 !== b.complete && j.showLoading();
        },
        _loadAjax: function() {
            var b = j.coming;
            j.showLoading();
            j.ajaxLoad = i.ajax(i.extend({}, b.ajax, { url: b.href, error: function(f, l) { j.coming && "abort" !== l ? j._error("ajax", f) : j.hideLoading() }, success: function(l, f) { "success" === f && (b.content = l, j._afterLoad()) } }));
        },
        _loadIframe: function() {
            var b = j.coming, f = i(b.tpl.iframe.replace(/\{rnd\}/g, (new Date).getTime())).attr("scrolling", A ? "auto" : b.iframe.scrolling).attr("src", b.href);
            i(b.wrap).bind("onReset", function() {
                try {
                    i(this).find("iframe").hide().attr("src", "//about:blank").end().empty();
                } catch (l) {
                }
            });
            b.iframe.preload && (j.showLoading(), f.one("load", function() {
                i(this).data("ready", 1);
                A || i(this).bind("load.fb", j.update);
                i(this).parents(".fancybox-wrap").width("100%").removeClass("fancybox-tmp").show();
                j._afterLoad();
            }));
            b.content = f.appendTo(b.inner);
            b.iframe.preload || j._afterLoad();
        },
        _preloadImages: function() {
            var b = j.group, q = j.current, p = b.length, r = q.preload ? Math.min(q.preload, p - 1) : 0, n, l;
            for (l = 1; l <= r; l += 1) {
                n = b[(q.index + l) % p], "image" === n.type && n.href && ((new Image).src = n.href);
            }
        },
        _afterLoad: function() {
            var b = j.coming, q = j.current, p, r, f, n, l;
            j.hideLoading();
            if (b && !1 !== j.isActive) {
                if (!1 === j.trigger("afterLoad", b, q)) {
                    b.wrap.stop(!0).trigger("onReset").remove(), j.coming = null;
                } else {
                    q && (j.trigger("beforeChange", q), q.wrap.stop(!0).removeClass("fancybox-opened").find(".fancybox-item, .fancybox-nav").remove());
                    j.unbindEvents();
                    p = b.content;
                    r = b.type;
                    f = b.scrolling;
                    i.extend(j, { wrap: b.wrap, skin: b.skin, outer: b.outer, inner: b.inner, current: b, previous: q });
                    n = b.href;
                    switch (r) {
                    case"inline":
                    case"ajax":
                    case"html":
                        b.selector ? p = i("<div>").html(p).find(b.selector) : y(p) && (p.data("fancybox-placeholder") || p.data("fancybox-placeholder", i("<div class=\"fancybox-placeholder\"></div>").insertAfter(p).hide()), p = p.show().detach(), b.wrap.bind("onReset", function() { i(this).find(p).length && p.hide().replaceAll(p.data("fancybox-placeholder")).data("fancybox-placeholder", !1) }));
                        break;
                    case"image":
                        p = b.tpl.image.replace("{href}", n);
                        break;
                    case"swf":
                        p = "<object id=\"fancybox-swf\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"100%\" height=\"100%\"><param name=\"movie\" value=\"" + n + "\"></param>", l = "", i.each(b.swf, function(t, s) {
                            p += "<param name=\"" + t + "\" value=\"" + s + "\"></param>";
                            l += " " + t + "=\"" + s + "\"";
                        }), p += "<embed src=\"" + n + "\" type=\"application/x-shockwave-flash\" width=\"100%\" height=\"100%\"" + l + "></embed></object>";
                    }
                    (!y(p) || !p.parent().is(b.inner)) && b.inner.append(p);
                    j.trigger("beforeShow");
                    b.inner.css("overflow", "yes" === f ? "scroll" : "no" === f ? "hidden" : f);
                    j._setDimension();
                    j.reposition();
                    j.isOpen = !1;
                    j.coming = null;
                    j.bindEvents();
                    if (j.isOpened) {
                        if (q.prevMethod) {
                            j.transitions[q.prevMethod]();
                        }
                    } else {
                        i(".fancybox-wrap").not(b.wrap).stop(!0).trigger("onReset").remove();
                    }
                    j.transitions[j.isOpened ? b.nextMethod : b.openMethod]();
                    j._preloadImages();
                }
            }
        },
        _setDimension: function() {
            var ad = j.getViewport(), ab = 0, aa = !1, ac = !1, aa = j.wrap, W = j.skin, Z = j.inner, Y = j.current, ac = Y.width, X = Y.height, V = Y.minWidth, K = Y.minHeight, U = Y.maxWidth, T = Y.maxHeight, N = Y.scrolling, R = Y.scrollOutside ? Y.scrollbarWidth : 0, l = Y.margin, f = h(l[1] + l[3]), P = h(l[0] + l[2]), I, b, L, O, S, J, Q, M, w;
            aa.add(W).add(Z).width("auto").height("auto").removeClass("fancybox-tmp");
            l = h(W.outerWidth(!0) - W.width());
            I = h(W.outerHeight(!0) - W.height());
            b = f + l;
            L = P + I;
            O = z(ac) ? (ad.w - b) * h(ac) / 100 : ac;
            S = z(X) ? (ad.h - L) * h(X) / 100 : X;
            if ("iframe" === Y.type) {
                if (w = Y.content, Y.autoHeight && 1 === w.data("ready")) {
                    try {
                        w[0].contentWindow.document.location && (Z.width(O).height(9999), J = w.contents().find("body"), R && J.css("overflow-x", "hidden"), S = J.outerHeight(!0));
                    } catch (E) {
                    }
                }
            } else {
                if (Y.autoWidth || Y.autoHeight) {
                    Z.addClass("fancybox-tmp"), Y.autoWidth || Z.width(O), Y.autoHeight || Z.height(S), Y.autoWidth && (O = Z.width()), Y.autoHeight && (S = Z.height()), Z.removeClass("fancybox-tmp");
                }
            }
            ac = h(O);
            X = h(S);
            M = O / S;
            V = h(z(V) ? h(V, "w") - b : V);
            U = h(z(U) ? h(U, "w") - b : U);
            K = h(z(K) ? h(K, "h") - L : K);
            T = h(z(T) ? h(T, "h") - L : T);
            J = U;
            Q = T;
            Y.fitToView && (U = Math.min(ad.w - b, U), T = Math.min(ad.h - L, T));
            b = ad.w - f;
            P = ad.h - P;
            Y.aspectRatio ? (ac > U && (ac = U, X = h(ac / M)), X > T && (X = T, ac = h(X * M)), ac < V && (ac = V, X = h(ac / M)), X < K && (X = K, ac = h(X * M))) : (ac = Math.max(V, Math.min(ac, U)), Y.autoHeight && "iframe" !== Y.type && (Z.width(ac), X = Z.height()), X = Math.max(K, Math.min(X, T)));
            if (Y.fitToView) {
                if (Z.width(ac).height(X), aa.width(ac + l), ad = aa.width(), f = aa.height(), Y.aspectRatio) {
                    for (; (ad > b || f > P) && (ac > V && X > K) && !(19 < ab++);) {
                        X = Math.max(K, Math.min(T, X - 10)), ac = h(X * M), ac < V && (ac = V, X = h(ac / M)), ac > U && (ac = U, X = h(ac / M)), Z.width(ac).height(X), aa.width(ac + l), ad = aa.width(), f = aa.height();
                    }
                } else {
                    ac = Math.max(V, Math.min(ac, ac - (ad - b))), X = Math.max(K, Math.min(X, X - (f - P)));
                }
            }
            R && ("auto" === N && X < S && ac + l + R < b) && (ac += R);
            Z.width(ac).height(X);
            aa.width(ac + l);
            ad = aa.width();
            f = aa.height();
            aa = (ad > b || f > P) && ac > V && X > K;
            ac = Y.aspectRatio ? ac < J && X < Q && ac < O && X < S : (ac < J || X < Q) && (ac < O || X < S);
            i.extend(Y, { dim: { width: m(ad), height: m(f) }, origWidth: O, origHeight: S, canShrink: aa, canExpand: ac, wPadding: l, hPadding: I, wrapSpace: f - W.outerHeight(!0), skinSpace: W.height() - X });
            !w && (Y.autoHeight && X > K && X < T && !ac) && Z.height("auto");
        },
        _getPosition: function(b) {
            var q = j.current, p = j.getViewport(), r = q.margin, n = j.wrap.width() + r[1] + r[3], l = j.wrap.height() + r[0] + r[2], r = { position: "absolute", top: r[0], left: r[3] };
            q.autoCenter && q.fixed && !b && l <= p.h && n <= p.w ? r.position = "fixed" : q.locked || (r.top += p.y, r.left += p.x);
            r.top = m(Math.max(r.top, r.top + (p.h - l) * q.topRatio));
            r.left = m(Math.max(r.left, r.left + (p.w - n) * q.leftRatio));
            return r;
        },
        _afterZoomIn: function() {
            var b = j.current;
            b && (j.isOpen = j.isOpened = !0, j.wrap.css("overflow", "visible").addClass("fancybox-opened"), j.update(), (b.closeClick || b.nextClick && 1 < j.group.length) && j.inner.css("cursor", "pointer").bind("click.fb", function(f) { !i(f.target).is("a") && !i(f.target).parent().is("a") && (f.preventDefault(), j[b.closeClick ? "close" : "next"]()) }), b.closeBtn && i(b.tpl.closeBtn).appendTo(j.skin).bind("click.fb", function(f) {
                f.preventDefault();
                j.close();
            }), b.arrows && 1 < j.group.length && ((b.loop || 0 < b.index) && i(b.tpl.prev).appendTo(j.outer).bind("click.fb", j.prev), (b.loop || b.index < j.group.length - 1) && i(b.tpl.next).appendTo(j.outer).bind("click.fb", j.next)), j.trigger("afterShow"), !b.loop && b.index === b.group.length - 1 ? j.play(!1) : j.opts.autoPlay && !j.player.isActive && (j.opts.autoPlay = !1, j.play()));
        },
        _afterZoomOut: function(b) {
            b = b || j.current;
            i(".fancybox-wrap").trigger("onReset").remove();
            i.extend(j, { group: {}, opts: {}, router: !1, current: null, isActive: !1, isOpened: !1, isOpen: !1, isClosing: !1, wrap: null, skin: null, outer: null, inner: null });
            j.trigger("afterClose", b);
        }
    });
    j.transitions = {
        getOrigPosition: function() {
            var v = j.current, s = v.element, r = v.orig, t = {}, q = 50, p = 50, n = v.hPadding, l = v.wPadding, b = j.getViewport();
            !r && (v.isDom && s.is(":visible")) && (r = s.find("img:first"), r.length || (r = s));
            y(r) ? (t = r.offset(), r.is("img") && (q = r.outerWidth(), p = r.outerHeight())) : (t.top = b.y + (b.h - p) * v.topRatio, t.left = b.x + (b.w - q) * v.leftRatio);
            if ("fixed" === j.wrap.css("position") || v.locked) {
                t.top -= b.y, t.left -= b.x;
            }
            return t = { top: m(t.top - n * v.topRatio), left: m(t.left - l * v.leftRatio), width: m(q + l), height: m(p + n) };
        },
        step: function(b, r) {
            var q, s, p = r.prop;
            s = j.current;
            var n = s.wrapSpace, l = s.skinSpace;
            if ("width" === p || "height" === p) {
                q = r.end === r.start ? 1 : (b - r.start) / (r.end - r.start), j.isClosing && (q = 1 - q), s = "width" === p ? s.wPadding : s.hPadding, s = b - s, j.skin[p](h("width" === p ? s : s - n * q)), j.inner[p](h("width" === p ? s : s - n * q - l * q));
            }
        },
        zoomIn: function() {
            var b = j.current, n = b.pos, l = b.openEffect, p = "elastic" === l, f = i.extend({ opacity: 1 }, n);
            delete f.position;
            p ? (n = this.getOrigPosition(), b.openOpacity && (n.opacity = 0.1)) : "fade" === l && (n.opacity = 0.1);
            j.wrap.css(n).animate(f, { duration: "none" === l ? 0 : b.openSpeed, easing: b.openEasing, step: p ? this.step : null, complete: j._afterZoomIn });
        },
        zoomOut: function() {
            var b = j.current, l = b.closeEffect, f = "elastic" === l, n = { opacity: 0.1 };
            f && (n = this.getOrigPosition(), b.closeOpacity && (n.opacity = 0.1));
            j.wrap.animate(n, { duration: "none" === l ? 0 : b.closeSpeed, easing: b.closeEasing, step: f ? this.step : null, complete: j._afterZoomOut });
        },
        changeIn: function() {
            var b = j.current, q = b.nextEffect, p = b.pos, r = { opacity: 1 }, n = j.direction, l;
            p.opacity = 0.1;
            "elastic" === q && (l = "down" === n || "up" === n ? "top" : "left", "down" === n || "right" === n ? (p[l] = m(h(p[l]) - 200), r[l] = "+=200px") : (p[l] = m(h(p[l]) + 200), r[l] = "-=200px"));
            "none" === q ? j._afterZoomIn() : j.wrap.css(p).animate(r, { duration: b.nextSpeed, easing: b.nextEasing, complete: j._afterZoomIn });
        },
        changeOut: function() {
            var b = j.previous, l = b.prevEffect, f = { opacity: 0.1 }, n = j.direction;
            "elastic" === l && (f["down" === n || "up" === n ? "top" : "left"] = ("up" === n || "left" === n ? "-" : "+") + "=200px");
            b.wrap.animate(f, { duration: "none" === l ? 0 : b.prevSpeed, easing: b.prevEasing, complete: function() { i(this).trigger("onReset").remove() } });
        }
    };
    j.helpers.overlay = {
        defaults: { closeClick: !0, speedOut: 200, showEarly: !0, css: {}, locked: !A, fixed: !0 }, overlay: null, fixed: !1, el: i("html"),
        create: function(b) {
            b = i.extend({}, this.defaults, b);
            this.overlay && this.close();
            this.overlay = i("<div class=\"fancybox-overlay\"></div>").appendTo(j.coming ? j.coming.parent : b.parent);
            this.fixed = !1;
            b.fixed && j.defaults.fixed && (this.overlay.addClass("fancybox-overlay-fixed"), this.fixed = !0);
        },
        open: function(b) {
            var f = this;
            b = i.extend({}, this.defaults, b);
            this.overlay ? this.overlay.unbind(".overlay").width("auto").height("auto") : this.create(b);
            this.fixed || (g.bind("resize.overlay", i.proxy(this.update, this)), this.update());
            b.closeClick && this.overlay.bind("click.overlay", function(l) {
                if (i(l.target).hasClass("fancybox-overlay")) {
                    return j.isActive ? j.close() : f.close(), !1;
                }
            });
            this.overlay.css(b.css).show();
        },
        close: function() {
            var l, f;
            g.unbind("resize.overlay");
            this.el.hasClass("fancybox-lock") && (i(".fancybox-margin").removeClass("fancybox-margin"), l = g.scrollTop(), f = g.scrollLeft(), this.el.removeClass("fancybox-lock"), g.scrollTop(l).scrollLeft(f));
            i(".fancybox-overlay").remove().hide();
            i.extend(this, { overlay: null, fixed: !1 });
        },
        update: function() {
            var l = "100%", f;
            this.overlay.width(l).height("100%");
            o ? (f = Math.max(x.documentElement.offsetWidth, x.body.offsetWidth), e.width() > f && (l = e.width())) : e.width() > g.width() && (l = e.width());
            this.overlay.width(l).height(e.height());
        },
        onReady: function(l, f) {
            var n = this.overlay;
            i(".fancybox-overlay").stop(!0, !0);
            n || this.create(l);
            l.locked && (this.fixed && f.fixed) && (n || (this.margin = e.height() > g.height() ? i("html").css("margin-right").replace("px", "") : !1), f.locked = this.overlay.append(f.wrap), f.fixed = !1);
            !0 === l.showEarly && this.beforeShow.apply(this, arguments);
        },
        beforeShow: function(l, f) {
            var n, p;
            f.locked && (!1 !== this.margin && (i("*").filter(function() { return"fixed" === i(this).css("position") && !i(this).hasClass("fancybox-overlay") && !i(this).hasClass("fancybox-wrap") }).addClass("fancybox-margin"), this.el.addClass("fancybox-margin")), n = g.scrollTop(), p = g.scrollLeft(), this.el.addClass("fancybox-lock"), g.scrollTop(n).scrollLeft(p));
            this.open(l);
        },
        onUpdate: function() { this.fixed || this.update() },
        afterClose: function(b) { this.overlay && !j.coming && this.overlay.fadeOut(b.speedOut, i.proxy(this.close, this)) }
    };
    j.helpers.title = {
        defaults: { type: "float", position: "bottom" },
        beforeShow: function(b) {
            var l = j.current, f = l.title, n = b.type;
            i.isFunction(f) && (f = f.call(l.element, l));
            if (c(f) && "" !== i.trim(f)) {
                l = i("<div class=\"fancybox-title fancybox-title-" + n + "-wrap\">" + f + "</div>");
                switch (n) {
                case"inside":
                    n = j.skin;
                    break;
                case"outside":
                    n = j.wrap;
                    break;
                case"over":
                    n = j.inner;
                    break;
                default:
                    n = j.skin, l.appendTo("body"), o && l.width(l.width()), l.wrapInner("<span class=\"child\"></span>"), j.current.margin[2] += Math.abs(h(l.css("margin-bottom")));
                }
                l["top" === b.position ? "prependTo" : "appendTo"](n);
            }
        }
    };
    i.fn.fancybox = function(b) {
        var n, l = i(this), p = this.selector || "",
            f = function(v) {
                var t = i(this).blur(), s = n, r, q;
                !v.ctrlKey && (!v.altKey && !v.shiftKey && !v.metaKey) && !t.is(".fancybox-wrap") && (r = b.groupAttr || "data-fancybox-group", q = t.attr(r), q || (r = "rel", q = t.get(0)[r]), q && ("" !== q && "nofollow" !== q) && (t = p.length ? i(p) : l, t = t.filter("[" + r + "=\"" + q + "\"]"), s = t.index(this)), b.index = s, !1 !== j.open(t, b) && v.preventDefault());
            };
        b = b || {};
        n = b.index || 0;
        !p || !1 === b.live ? l.unbind("click.fb-start").bind("click.fb-start", f) : e.undelegate(p, "click.fb-start").delegate(p + ":not('.fancybox-item, .fancybox-nav')", "click.fb-start", f);
        this.filter("[data-fancybox-start=1]").trigger("click");
        return this;
    };
    e.ready(function() {
        var b, l;
        i.scrollbarWidth === u && (i.scrollbarWidth = function() {
            var p = i("<div style=\"width:50px;height:50px;overflow:auto\"><div/></div>").appendTo("body"), n = p.children(), n = n.innerWidth() - n.height(99).innerWidth();
            p.remove();
            return n;
        });
        if (i.support.fixedPosition === u) {
            b = i.support;
            l = i("<div style=\"position:fixed;top:20px;\"></div>").appendTo("body");
            var f = 20 === l[0].offsetTop || 15 === l[0].offsetTop;
            l.remove();
            b.fixedPosition = f;
        }
        i.extend(j.defaults, { scrollbarWidth: i.scrollbarWidth(), fixed: i.support.fixedPosition, parent: i("body") });
        b = i(a).width();
        k.addClass("fancybox-lock-test");
        l = i(a).width();
        k.removeClass("fancybox-lock-test");
        i("<style type='text/css'>.fancybox-margin{margin-right:" + (l - b) + "px;}</style>").appendTo("head");
    });
})(window, document, jQuery);
(function(b) {
    function a(c) { this.init(c) }

    a.prototype = {
        value: 0, size: 100, startAngle: -Math.PI, thickness: "auto", fill: { gradient: ["#3aeabb", "#fdd250"] }, emptyFill: "rgba(0, 0, 0, .1)", animation: { duration: 1200, easing: "circleProgressEasing" }, animationStartValue: 0, reverse: false, lineCap: "butt", constructor: a, el: null, canvas: null, ctx: null, radius: 0, arcFill: null, lastFrameValue: 0,
        init: function(c) {
            b.extend(this, c);
            this.radius = this.size / 2;
            this.initWidget();
            this.initFill();
            this.draw();
        },
        initWidget: function() {
            var c = this.canvas = this.canvas || b("<canvas>").prependTo(this.el)[0];
            c.width = this.size;
            c.height = this.size;
            this.ctx = c.getContext("2d");
        },
        initFill: function() {
            var m = this, l = this.fill, o = this.ctx, p = this.size;
            if (!l) {
                throw Error("The fill is not specified!");
            }
            if (l.color) {
                this.arcFill = l.color;
            }
            if (l.gradient) {
                var c = l.gradient;
                if (c.length == 1) {
                    this.arcFill = c[0];
                } else {
                    if (c.length > 1) {
                        var k = l.gradientAngle || 0, j = l.gradientDirection || [p / 2 * (1 - Math.cos(k)), p / 2 * (1 + Math.sin(k)), p / 2 * (1 + Math.cos(k)), p / 2 * (1 - Math.sin(k))];
                        var n = o.createLinearGradient.apply(o, j);
                        for (var f = 0; f < c.length; f++) {
                            var d = c[f], g = f / (c.length - 1);
                            if (b.isArray(d)) {
                                g = d[1];
                                d = d[0];
                            }
                            n.addColorStop(g, d);
                        }
                        this.arcFill = n;
                    }
                }
            }
            if (l.image) {
                var e;
                if (l.image instanceof Image) {
                    e = l.image;
                } else {
                    e = new Image();
                    e.src = l.image;
                }
                if (e.complete) {
                    h();
                } else {
                    e.onload = h;
                }
            }

            function h() {
                var i = b("<canvas>")[0];
                i.width = m.size;
                i.height = m.size;
                i.getContext("2d").drawImage(e, 0, 0, p, p);
                m.arcFill = m.ctx.createPattern(i, "no-repeat");
                m.drawFrame(m.lastFrameValue);
            }
        },
        draw: function() {
            if (this.animation) {
                this.drawAnimated(this.value);
            } else {
                this.drawFrame(this.value);
            }
        },
        drawFrame: function(c) {
            this.lastFrameValue = c;
            this.ctx.clearRect(0, 0, this.size, this.size);
            this.drawEmptyArc(c);
            this.drawArc(c);
        },
        drawArc: function(e) {
            var d = this.ctx, g = this.radius, f = this.getThickness(), c = this.startAngle;
            d.save();
            d.beginPath();
            if (!this.reverse) {
                d.arc(g, g, g - f / 2, c, c + Math.PI * 2 * e);
            } else {
                d.arc(g, g, g - f / 2, c - Math.PI * 2 * e, c);
            }
            d.lineWidth = f;
            d.lineCap = this.lineCap;
            d.strokeStyle = this.arcFill;
            d.stroke();
            d.restore();
        },
        drawEmptyArc: function(e) {
            var d = this.ctx, g = this.radius, f = this.getThickness(), c = this.startAngle;
            if (e < 1) {
                d.save();
                d.beginPath();
                if (e <= 0) {
                    d.arc(g, g, g - f / 2, 0, Math.PI * 2);
                } else {
                    if (!this.reverse) {
                        d.arc(g, g, g - f / 2, c + Math.PI * 2 * e, c);
                    } else {
                        d.arc(g, g, g - f / 2, c, c - Math.PI * 2 * e);
                    }
                }
                d.lineWidth = f;
                d.strokeStyle = this.emptyFill;
                d.stroke();
                d.restore();
            }
        },
        drawAnimated: function(d) {
            var c = this, e = this.el;
            e.trigger("circle-animation-start");
            b(this.canvas).stop(true, true).css({ animationProgress: 0 }).animate({ animationProgress: 1 }, b.extend({}, this.animation, {
                step: function(g) {
                    var f = c.animationStartValue * (1 - g) + d * g;
                    c.drawFrame(f);
                    e.trigger("circle-animation-progress", [g, f]);
                },
                complete: function() { e.trigger("circle-animation-end") }
            }));
        },
        getThickness: function() { return b.isNumeric(this.thickness) ? this.thickness : this.size / 14 }
    };
    b.circleProgress = { defaults: a.prototype };
    b.easing.circleProgressEasing = function(f, g, e, i, h) {
        if ((g /= h / 2) < 1) {
            return i / 2 * g * g * g + e;
        }
        return i / 2 * ((g -= 2) * g * g + 2) + e;
    };
    b.fn.circleProgress = function(d) {
        var c = "circle-progress";
        if (d == "widget") {
            var e = this.data(c);
            return e && e.canvas;
        }
        return this.each(function() {
            var h = b(this), f = h.data(c), g = b.isPlainObject(d) ? d : {};
            if (f) {
                f.init(g);
            } else {
                g.el = h;
                f = new a(g);
                h.data(c, f);
            }
        });
    };
})(jQuery);
(function(f) {
    function b() {
        this.regional = [];
        this.regional[""] = { labels: ["Years", "Months", "Weeks", "Days", "Hours", "Mins", "Secs"], labels1: ["Year", "Month", "Week", "Day", "Hour", "Min", "Secs"], compactLabels: ["y", "m", "w", "d"], whichLabels: null, digits: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"], timeSeparator: ":", isRTL: false };
        this._defaults = { until: null, since: null, timezone: null, serverSync: null, format: "dHMS", layout: "", compact: false, significant: 0, description: "", expiryUrl: "", expiryText: "", alwaysExpire: false, onExpiry: null, onTick: null, tickInterval: 1 };
        f.extend(this._defaults, this.regional[""]);
        this._serverSyncs = [];

        function n(p) {
            var q = (p < 1000000000000 ? (q = performance.now ? (performance.now() + performance.timing.navigationStart) : Date.now()) : p || new Date().getTime());
            if (q - m >= 1000) {
                g._updateTargets();
                m = q;
            }
            o(n);
        }

        var o = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || null;
        var m = 0;
        if (!o || f.noRequestAnimationFrame) {
            f.noRequestAnimationFrame = null;
            setInterval(function() { g._updateTargets() }, 980);
        } else {
            m = window.mozAnimationStartTime || new Date().getTime();
            o(n);
        }
    }

    var c = 0;
    var h = 1;
    var d = 2;
    var a = 3;
    var l = 4;
    var i = 5;
    var e = 6;
    f.extend(b.prototype, {
        markerClassName: "hasCountdown", propertyName: "countdown", _rtlClass: "countdown_rtl", _sectionClass: "countdown_section", _amountClass: "countdown_amount", _rowClass: "countdown_row", _holdingClass: "countdown_holding", _showClass: "countdown_show", _descrClass: "countdown_descr", _timerTargets: [],
        setDefaults: function(m) {
            this._resetExtraLabels(this._defaults, m);
            f.extend(this._defaults, m || {});
        },
        UTCDate: function(o, s, q, t, u, n, p, m) {
            if (typeof s == "object" && s.constructor == Date) {
                m = s.getMilliseconds();
                p = s.getSeconds();
                n = s.getMinutes();
                u = s.getHours();
                t = s.getDate();
                q = s.getMonth();
                s = s.getFullYear();
            }
            var r = new Date();
            r.setUTCFullYear(s);
            r.setUTCDate(1);
            r.setUTCMonth(q || 0);
            r.setUTCDate(t || 1);
            r.setUTCHours(u || 0);
            r.setUTCMinutes((n || 0) - (Math.abs(o) < 30 ? o * 60 : o));
            r.setUTCSeconds(p || 0);
            r.setUTCMilliseconds(m || 0);
            return r;
        },
        periodsToSeconds: function(m) { return m[0] * 31557600 + m[1] * 2629800 + m[2] * 604800 + m[3] * 86400 + m[4] * 3600 + m[5] * 60 + m[6] },
        _attachPlugin: function(o, m) {
            o = f(o);
            if (o.hasClass(this.markerClassName)) {
                return;
            }
            var n = { options: f.extend({}, this._defaults), _periods: [0, 0, 0, 0, 0, 0, 0] };
            o.addClass(this.markerClassName).data(this.propertyName, n);
            this._optionPlugin(o, m);
        },
        _addTarget: function(m) {
            if (!this._hasTarget(m)) {
                this._timerTargets.push(m);
            }
        },
        _hasTarget: function(m) { return(f.inArray(m, this._timerTargets) > -1) },
        _removeTarget: function(m) { this._timerTargets = f.map(this._timerTargets, function(n) { return(n == m ? null : n) }) },
        _updateTargets: function() {
            for (var m = this._timerTargets.length - 1; m >= 0; m--) {
                this._updateCountdown(this._timerTargets[m]);
            }
        },
        _optionPlugin: function(r, o, q) {
            r = f(r);
            var p = r.data(this.propertyName);
            if (!o || (typeof o == "string" && q == null)) {
                var n = o;
                o = (p || {}).options;
                return(o && n ? o[n] : o);
            }
            if (!r.hasClass(this.markerClassName)) {
                return;
            }
            o = o || {};
            if (typeof o == "string") {
                var n = o;
                o = {};
                o[n] = q;
            }
            this._resetExtraLabels(p.options, o);
            f.extend(p.options, o);
            this._adjustSettings(r, p);
            var m = new Date();
            if ((p._since && p._since < m) || (p._until && p._until > m)) {
                this._addTarget(r);
            }
            this._updateCountdown(r, p);
        },
        _updateCountdown: function(r, q) {
            var m = f(r);
            q = q || m.data(this.propertyName);
            if (!q) {
                return;
            }
            m.html(this._generateHTML(q)).toggleClass(this._rtlClass, q.options.isRTL);
            if (f.isFunction(q.options.onTick)) {
                var p = q._hold != "lap" ? q._periods : this._calculatePeriods(q, q._show, q.options.significant, new Date());
                if (q.options.tickInterval == 1 || this.periodsToSeconds(p) % q.options.tickInterval == 0) {
                    q.options.onTick.apply(r, [p]);
                }
            }
            var n = q._hold != "pause" && (q._since ? q._now.getTime() < q._since.getTime() : q._now.getTime() >= q._until.getTime());
            if (n && !q._expiring) {
                q._expiring = true;
                if (this._hasTarget(r) || q.options.alwaysExpire) {
                    this._removeTarget(r);
                    if (f.isFunction(q.options.onExpiry)) {
                        q.options.onExpiry.apply(r, []);
                    }
                    if (q.options.expiryText) {
                        var o = q.options.layout;
                        q.options.layout = q.options.expiryText;
                        this._updateCountdown(r, q);
                        q.options.layout = o;
                    }
                    if (q.options.expiryUrl) {
                        window.location = q.options.expiryUrl;
                    }
                }
                q._expiring = false;
            } else {
                if (q._hold == "pause") {
                    this._removeTarget(r);
                }
            }
            m.data(this.propertyName, q);
        },
        _resetExtraLabels: function(p, m) {
            var o = false;
            for (var q in m) {
                if (q != "whichLabels" && q.match(/[Ll]abels/)) {
                    o = true;
                    break;
                }
            }
            if (o) {
                for (var q in p) {
                    if (q.match(/[Ll]abels[02-9]/)) {
                        p[q] = null;
                    }
                }
            }
        },
        _adjustSettings: function(t, s) {
            var o;
            var n = 0;
            var m = null;
            for (var q = 0; q < this._serverSyncs.length; q++) {
                if (this._serverSyncs[q][0] == s.options.serverSync) {
                    m = this._serverSyncs[q][1];
                    break;
                }
            }
            if (m != null) {
                n = (s.options.serverSync ? m : 0);
                o = new Date();
            } else {
                var p = (f.isFunction(s.options.serverSync) ? s.options.serverSync.apply(t, []) : null);
                o = new Date();
                n = (p ? o.getTime() - p.getTime() : 0);
                this._serverSyncs.push([s.options.serverSync, n]);
            }
            var r = s.options.timezone;
            r = (r == null ? -o.getTimezoneOffset() : r);
            s._since = s.options.since;
            if (s._since != null) {
                s._since = this.UTCDate(r, this._determineTime(s._since, null));
                if (s._since && n) {
                    s._since.setMilliseconds(s._since.getMilliseconds() + n);
                }
            }
            s._until = this.UTCDate(r, this._determineTime(s.options.until, o));
            if (n) {
                s._until.setMilliseconds(s._until.getMilliseconds() + n);
            }
            s._show = this._determineShow(s);
        },
        _destroyPlugin: function(m) {
            m = f(m);
            if (!m.hasClass(this.markerClassName)) {
                return;
            }
            this._removeTarget(m[0]);
            m.removeClass(this.markerClassName).empty().removeData(this.propertyName);
        },
        _pausePlugin: function(m) { this._hold(m, "pause") },
        _lapPlugin: function(m) { this._hold(m, "lap") },
        _resumePlugin: function(m) { this._hold(m, null) },
        _hold: function(p, o) {
            var n = f.data(p, this.propertyName);
            if (n) {
                if (n._hold == "pause" && !o) {
                    n._periods = n._savePeriods;
                    var m = (n._since ? "-" : "+");
                    n[n._since ? "_since" : "_until"] = this._determineTime(m + n._periods[0] + "y" + m + n._periods[1] + "o" + m + n._periods[2] + "w" + m + n._periods[3] + "d" + m + n._periods[4] + "h" + m + n._periods[5] + "m" + m + n._periods[6] + "s");
                    this._addTarget(p);
                }
                n._hold = o;
                n._savePeriods = (o == "pause" ? n._periods : null);
                f.data(p, this.propertyName, n);
                this._updateCountdown(p, n);
            }
        },
        _getTimesPlugin: function(n) {
            var m = f.data(n, this.propertyName);
            return(!m ? null : (!m._hold ? m._periods : this._calculatePeriods(m, m._show, m.options.significant, new Date())));
        },
        _determineTime: function(p, m) {
            var o = function(s) {
                var r = new Date();
                r.setTime(r.getTime() + s * 1000);
                return r;
            };
            var n = function(v) {
                v = v.toLowerCase();
                var s = new Date();
                var z = s.getFullYear();
                var x = s.getMonth();
                var A = s.getDate();
                var u = s.getHours();
                var t = s.getMinutes();
                var r = s.getSeconds();
                var y = /([+-]?[0-9]+)\s*(s|m|h|d|w|o|y)?/g;
                var w = y.exec(v);
                while (w) {
                    switch (w[2] || "s") {
                    case"s":
                        r += parseInt(w[1], 10);
                        break;
                    case"m":
                        t += parseInt(w[1], 10);
                        break;
                    case"h":
                        u += parseInt(w[1], 10);
                        break;
                    case"d":
                        A += parseInt(w[1], 10);
                        break;
                    case"w":
                        A += parseInt(w[1], 10) * 7;
                        break;
                    case"o":
                        x += parseInt(w[1], 10);
                        A = Math.min(A, g._getDaysInMonth(z, x));
                        break;
                    case"y":
                        z += parseInt(w[1], 10);
                        A = Math.min(A, g._getDaysInMonth(z, x));
                        break;
                    }
                    w = y.exec(v);
                }
                return new Date(z, x, A, u, t, r, 0);
            };
            var q = (p == null ? m : (typeof p == "string" ? n(p) : (typeof p == "number" ? o(p) : p)));
            if (q) {
                q.setMilliseconds(0);
            }
            return q;
        },
        _getDaysInMonth: function(m, n) { return 32 - new Date(m, n, 32).getDate() },
        _normalLabels: function(m) { return m },
        _generateHTML: function(q) {
            var x = this;
            q._periods = (q._hold ? q._periods : this._calculatePeriods(q, q._show, q.options.significant, new Date()));
            var u = false;
            var n = 0;
            var p = q.options.significant;
            var w = f.extend({}, q._show);
            for (var t = c; t <= e; t++) {
                u |= (q._show[t] == "?" && q._periods[t] > 0);
                w[t] = (q._show[t] == "?" && !u ? null : q._show[t]);
                n += (w[t] ? 1 : 0);
                p -= (q._periods[t] > 0 ? 1 : 0);
            }
            var v = [false, false, false, false, false, false, false];
            for (var t = e; t >= c; t--) {
                if (q._show[t]) {
                    if (q._periods[t]) {
                        v[t] = true;
                    } else {
                        v[t] = p > 0;
                        p--;
                    }
                }
            }
            var r = (q.options.compact ? q.options.compactLabels : q.options.labels);
            var m = q.options.whichLabels || this._normalLabels;
            var s = function(z) {
                var y = q.options["compactLabels" + m(q._periods[z])];
                return(w[z] ? x._translateDigits(q, q._periods[z]) + (y ? y[z] : r[z]) + " " : "");
            };
            var o = function(A) {
                var z = q.options["labels" + m(q._periods[A])];
                var y = x._translateDigits(q, q._periods[A]);
                if (y.length == 1) {
                    y = "0" + y;
                }
                return((!q.options.significant && w[A]) || (q.options.significant && v[A]) ? "<span class=\"" + g._sectionClass + "\"><em>" + (z ? z[A] : r[A]) + "</em><span class=\"" + g._amountClass + "\">" + y + "</span></span>" : "");
            };
            return(q.options.layout ? this._buildLayout(q, w, q.options.layout, q.options.compact, q.options.significant, v) : ((q.options.compact ? "<span class=\"" + this._rowClass + " " + this._amountClass + (q._hold ? " " + this._holdingClass : "") + "\">" + s(c) + s(h) + s(d) + s(a) + (w[l] ? this._minDigits(q, q._periods[l], 2) : "") + (w[i] ? (w[l] ? q.options.timeSeparator : "") + this._minDigits(q, q._periods[i], 2) : "") + (w[e] ? (w[l] || w[i] ? q.options.timeSeparator : "") + this._minDigits(q, q._periods[e], 2) : "") : "<span class=\"" + this._rowClass + " " + this._showClass + (q.options.significant || n) + (q._hold ? " " + this._holdingClass : "") + "\">" + o(c) + o(h) + o(d) + o(a) + o(l) + o(i) + o(e)) + "</span>" + (q.options.description ? "<span class=\"" + this._rowClass + " " + this._descrClass + "\">" + q.options.description + "</span>" : "")));
        },
        _buildLayout: function(r, y, t, v, z, x) {
            var s = r.options[v ? "compactLabels" : "labels"];
            var n = r.options.whichLabels || this._normalLabels;
            var m = function(B) { return(r.options[(v ? "compactLabels" : "labels") + n(r._periods[B])] || s)[B] };
            var w = function(C, B) { return r.options.digits[Math.floor(C / B) % 10] };
            var o = { desc: r.options.description, sep: r.options.timeSeparator, yl: m(c), yn: this._minDigits(r, r._periods[c], 1), ynn: this._minDigits(r, r._periods[c], 2), ynnn: this._minDigits(r, r._periods[c], 3), y1: w(r._periods[c], 1), y10: w(r._periods[c], 10), y100: w(r._periods[c], 100), y1000: w(r._periods[c], 1000), ol: m(h), on: this._minDigits(r, r._periods[h], 1), onn: this._minDigits(r, r._periods[h], 2), onnn: this._minDigits(r, r._periods[h], 3), o1: w(r._periods[h], 1), o10: w(r._periods[h], 10), o100: w(r._periods[h], 100), o1000: w(r._periods[h], 1000), wl: m(d), wn: this._minDigits(r, r._periods[d], 1), wnn: this._minDigits(r, r._periods[d], 2), wnnn: this._minDigits(r, r._periods[d], 3), w1: w(r._periods[d], 1), w10: w(r._periods[d], 10), w100: w(r._periods[d], 100), w1000: w(r._periods[d], 1000), dl: m(a), dn: this._minDigits(r, r._periods[a], 1), dnn: this._minDigits(r, r._periods[a], 2), dnnn: this._minDigits(r, r._periods[a], 3), d1: w(r._periods[a], 1), d10: w(r._periods[a], 10), d100: w(r._periods[a], 100), d1000: w(r._periods[a], 1000), hl: m(l), hn: this._minDigits(r, r._periods[l], 1), hnn: this._minDigits(r, r._periods[l], 2), hnnn: this._minDigits(r, r._periods[l], 3), h1: w(r._periods[l], 1), h10: w(r._periods[l], 10), h100: w(r._periods[l], 100), h1000: w(r._periods[l], 1000), ml: m(i), mn: this._minDigits(r, r._periods[i], 1), mnn: this._minDigits(r, r._periods[i], 2), mnnn: this._minDigits(r, r._periods[i], 3), m1: w(r._periods[i], 1), m10: w(r._periods[i], 10), m100: w(r._periods[i], 100), m1000: w(r._periods[i], 1000), sl: m(e), sn: this._minDigits(r, r._periods[e], 1), snn: this._minDigits(r, r._periods[e], 2), snnn: this._minDigits(r, r._periods[e], 3), s1: w(r._periods[e], 1), s10: w(r._periods[e], 10), s100: w(r._periods[e], 100), s1000: w(r._periods[e], 1000) };
            var q = t;
            for (var p = c; p <= e; p++) {
                var u = "yowdhms".charAt(p);
                var A = new RegExp("\\{" + u + "<\\}(.*)\\{" + u + ">\\}", "g");
                q = q.replace(A, ((!z && y[p]) || (z && x[p]) ? "$1" : ""));
            }
            f.each(o, function(D, B) {
                var C = new RegExp("\\{" + D + "\\}", "g");
                q = q.replace(C, B);
            });
            return q;
        },
        _minDigits: function(o, n, m) {
            n = "" + n;
            if (n.length >= m) {
                return this._translateDigits(o, n);
            }
            n = "0000000000" + n;
            return this._translateDigits(o, n.substr(n.length - m));
        },
        _translateDigits: function(n, m) { return("" + m).replace(/[0-9]/g, function(o) { return n.options.digits[o] }) },
        _determineShow: function(n) {
            var o = n.options.format;
            var m = [];
            m[c] = (o.match("y") ? "?" : (o.match("Y") ? "!" : null));
            m[h] = (o.match("o") ? "?" : (o.match("O") ? "!" : null));
            m[d] = (o.match("w") ? "?" : (o.match("W") ? "!" : null));
            m[a] = (o.match("d") ? "?" : (o.match("D") ? "!" : null));
            m[l] = (o.match("h") ? "?" : (o.match("H") ? "!" : null));
            m[i] = (o.match("m") ? "?" : (o.match("M") ? "!" : null));
            m[e] = (o.match("s") ? "?" : (o.match("S") ? "!" : null));
            return m;
        },
        _calculatePeriods: function(p, D, t, n) {
            p._now = n;
            p._now.setMilliseconds(0);
            var r = new Date(p._now.getTime());
            if (p._since) {
                if (n.getTime() < p._since.getTime()) {
                    p._now = n = r;
                } else {
                    n = p._since;
                }
            } else {
                r.setTime(p._until.getTime());
                if (n.getTime() > p._until.getTime()) {
                    p._now = n = r;
                }
            }
            var m = [0, 0, 0, 0, 0, 0, 0];
            if (D[c] || D[h]) {
                var y = g._getDaysInMonth(n.getFullYear(), n.getMonth());
                var z = g._getDaysInMonth(r.getFullYear(), r.getMonth());
                var s = (r.getDate() == n.getDate() || (r.getDate() >= Math.min(y, z) && n.getDate() >= Math.min(y, z)));
                var C = function(F) { return(F.getHours() * 60 + F.getMinutes()) * 60 + F.getSeconds() };
                var u = Math.max(0, (r.getFullYear() - n.getFullYear()) * 12 + r.getMonth() - n.getMonth() + ((r.getDate() < n.getDate() && !s) || (s && C(r) < C(n)) ? -1 : 0));
                m[c] = (D[c] ? Math.floor(u / 12) : 0);
                m[h] = (D[h] ? u - m[c] * 12 : 0);
                n = new Date(n.getTime());
                var E = (n.getDate() == y);
                var q = g._getDaysInMonth(n.getFullYear() + m[c], n.getMonth() + m[h]);
                if (n.getDate() > q) {
                    n.setDate(q);
                }
                n.setFullYear(n.getFullYear() + m[c]);
                n.setMonth(n.getMonth() + m[h]);
                if (E) {
                    n.setDate(q);
                }
            }
            var x = Math.floor((r.getTime() - n.getTime()) / 1000);
            var o = function(G, F) {
                m[G] = (D[G] ? Math.floor(x / F) : 0);
                x -= m[G] * F;
            };
            o(d, 604800);
            o(a, 86400);
            o(l, 3600);
            o(i, 60);
            o(e, 1);
            if (x > 0 && !p._since) {
                var v = [1, 12, 4.3482, 7, 24, 60, 60];
                var w = e;
                var A = 1;
                for (var B = e; B >= c; B--) {
                    if (D[B]) {
                        if (m[w] >= A) {
                            m[w] = 0;
                            x = 1;
                        }
                        if (x > 0) {
                            m[B]++;
                            x = 0;
                            w = B;
                            A = 1;
                        }
                    }
                    A *= v[B];
                }
            }
            if (t) {
                for (var B = c; B <= e; B++) {
                    if (t && m[B]) {
                        t--;
                    } else {
                        if (!t) {
                            m[B] = 0;
                        }
                    }
                }
            }
            return m;
        }
    });
    var j = ["getTimes"];

    function k(n, m) {
        if (n == "option" && (m.length == 0 || (m.length == 1 && typeof m[0] == "string"))) {
            return true;
        }
        return f.inArray(n, j) > -1;
    }

    f.fn.countdown = function(n) {
        var m = Array.prototype.slice.call(arguments, 1);
        if (k(n, m)) {
            return g["_" + n + "Plugin"].apply(g, [this[0]].concat(m));
        }
        return this.each(function() {
            if (typeof n == "string") {
                if (!g["_" + n + "Plugin"]) {
                    throw"Unknown command: " + n;
                }
                g["_" + n + "Plugin"].apply(g, [this].concat(m));
            } else {
                g._attachPlugin(this, n || {});
            }
        });
    };
    var g = f.countdown = new b();
})(jQuery);
(function(a) {
    a.fn.popupWindow = function(b) {
        return this.each(function() {
            a(this).click(function() {
                a.fn.popupWindow.defaultSettings = { centerBrowser: 1, centerScreen: 0, height: 500, left: 0, location: 0, menubar: 0, resizable: 0, scrollbars: 0, status: 0, width: 500, windowName: null, windowURL: null, top: 0, toolbar: 0 };
                settings = a.extend({}, a.fn.popupWindow.defaultSettings, b || {});
                var c = "height=" + settings.height + ",width=" + settings.width + ",toolbar=" + settings.toolbar + ",scrollbars=" + settings.scrollbars + ",status=" + settings.status + ",resizable=" + settings.resizable + ",location=" + settings.location + ",menuBar=" + settings.menubar;
                settings.windowName = this.name || settings.windowName;
                settings.windowURL = a(this).data("link") || settings.windowURL;
                var d, e;
                if (settings.centerBrowser) {
                    if (a.browser.msie) {
                        d = (window.screenTop - 120) + ((((document.documentElement.clientHeight + 120) / 2) - (settings.height / 2)));
                        e = window.screenLeft + ((((document.body.offsetWidth + 20) / 2) - (settings.width / 2)));
                    } else {
                        d = window.screenY + (((window.outerHeight / 2) - (settings.height / 2)));
                        e = window.screenX + (((window.outerWidth / 2) - (settings.width / 2)));
                    }
                    window.open(settings.windowURL, settings.windowName, c + ",left=" + e + ",top=" + d).focus();
                } else {
                    if (settings.centerScreen) {
                        d = (screen.height - settings.height) / 2;
                        e = (screen.width - settings.width) / 2;
                        window.open(settings.windowURL, settings.windowName, c + ",left=" + e + ",top=" + d).focus();
                    } else {
                        window.open(settings.windowURL, settings.windowName, c + ",left=" + settings.left + ",top=" + settings.top).focus();
                    }
                }
                return false;
            });
        });
    };
})(jQuery);
(function(a, b) {
    var c = {
        menuUrl: "",
        initialize: function() {
            if (b(".progressbar").length) {
                b(".progressbar").each(function() {
                    var h = b(this).find(".circle");
                    var m = h.data("note");
                    var k = (parseFloat(m) / 10).toFixed(3);
                    var o = h.data("red");
                    var n = h.data("green");
                    var g = h.data("blue");
                    var l = h.find(".int");
                    var j = h.find(".dec");
                    h.circleProgress({ startAngle: -Math.PI / 2, value: k, thickness: 7, size: 70, fill: { color: "rgb(" + o + ", " + n + ", " + g + ")" }, emptyFill: "rgba(" + o + ", " + n + ", " + g + ", .3)" }).on("circle-animation-progress", function(t, q, p) {
                        var s = (p * 10).toFixed(2).toString().split(".");
                        var r = s[0];
                        var u = s[1];
                        l.text(r);
                        j.text("." + u);
                    }).stop();
                });
            }
            var d = a.location.hash;
            if (d) {
                b(".box-tab").hide();
                b(d).show("slow");
                b(".tab-link").removeClass("active");
                b(".tab-link[href$=" + d + "]").addClass("active");
            }
            b(".tab-link").on("click", function(h) {
                h.preventDefault();
                var g = this.hash;
                b(g).fadeIn(400).siblings().hide();
                b(".tab-link").removeClass("active");
                b(this).addClass("active");
                b("html, body").animate({ scrollTop: 0 }, 0, function() { a.location.hash = g });
            });
            b(".play-video").on("click", function(h) {
                var g = b(".box-video");
                g.prepend("<iframe src=\"//player.vimeo.com/video/88883554?title=0&amp;byline=0&amp;portrait=0&amp;color=3c948b&amp;autoplay=1\" width=\"500\" height=\"208\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>");
                g.fadeIn(300);
                h.preventDefault();
            });
            b(".close-video").on("click", function(g) { b(".box-video").fadeOut(400, function() { b("iframe", this).remove().fadeOut(300) }) });
            b(".bt-search").click(function() {
                if (b(".search-text").hasClass("visible")) {
                    b(".search-text").removeClass("visible");
                } else {
                    b(".search-text").addClass("visible");
                }
                setTimeout(function() { b(".search-text .text").trigger("focus") }, 200);
                setTimeout(function() { b(".search-text").removeClass("visible") }, 10000);
            });
            b("#search-text input").on("click", function() {
                var g = b(this);
                g.closest("form").attr("action", g.data("url"));
            });
            b(".js-open-menu-user").on("click", function(g) {
                g.stopPropagation();
                b("#nav-user").toggleClass("open");
                b(".box-overlay").addClass("open");
            });
            b(".nav-sidebar").on("click", function(g) { g.stopPropagation() });
            b(document).on("click", function() {
                b(".nav-sidebar").removeClass("open");
                b(".box-overlay").removeClass("open");
            });
            b(".js-close-nav").on("click", function() {
                b(".nav-sidebar").removeClass("open");
                b(".box-overlay").removeClass("open");
            });
            b("nav.search .dropdown").click(function() {
                var g = b(this).data("filter");
                if (b(this).hasClass("open")) {
                    b(this).removeClass("open");
                    b("#nav-filters").removeClass("open");
                    b("#nav-filters").slideToggle("normal");
                    setTimeout(function() { b("#nav-filters .filter").hide() }, 500);
                } else {
                    b("nav.search .dropdown").removeClass("open");
                    b(this).addClass("open");
                    if (b("#nav-filters").hasClass("open")) {
                        b("#nav-filters .filter").hide();
                        b("#nav-filters .filter-" + g).fadeIn();
                    } else {
                        b("#nav-filters").addClass("open");
                        b("#nav-filters .filter-" + g).fadeIn();
                        b("#nav-filters").slideToggle("fast");
                    }
                }
            });
            b(".js-order-az").click(function() {
                var j = b(this).data("list");
                var g = b("." + j + " ul");
                g.addClass("open");
                var h = g.children("li").get();
                h.sort(function(l, k) {
                    var n = b(l).text().toUpperCase();
                    var m = b(k).text().toUpperCase();
                    return(n < m) ? -1 : (n > m) ? 1 : 0;
                });
                b.each(h, function(k, l) { g.append(l) });
            });
            b(a).scroll(function() {
                var g = b(a).scrollTop();
                if (g > 48 && !b("#header").hasClass("style2")) {
                    b("nav.search").removeClass("open");
                    b("body").addClass("header-fixed");
                    b(".menu2 li .box-scroll").removeClass("open");
                    b(".menu2 li span").removeClass("active");
                    b(".nav-filters").addClass("hide");
                } else {
                    if (b("nav.search").hasClass("visible")) {
                        b("nav.search").addClass("open");
                    }
                    if (b(".nav-filters").hasClass("hide")) {
                        b(".nav-filters").removeClass("hide");
                    }
                    b("body").removeClass("header-fixed");
                }
                if (g > 300) {
                    b(".bt-pag.fixed").addClass("hide");
                } else {
                    b(".bt-pag.fixed").removeClass("hide");
                }
            });
            b(".s_like").live("click", function(g) { f(g, this) });

            function f(k, g) {
                k.preventDefault();
                var j = b(g);
                var l = j.find(".total");
                var h = parseInt(j.find(".total").text());
                if (j.hasClass("active")) {
                    h--;
                } else {
                    h++;
                }
                j.addClass("processing");
                j.find(".total").text(h);
                var m = j;
                b(".s_like").die("click");
                b.ajax({
                    type: "post", url: m.attr("data-url"),
                    success: function(o) {
                        if (m.hasClass("active")) {
                            var n = m.attr("data-url").replace("unlike", "like");
                            m.attr("data-url", n);
                            m.addClass("add_like");
                            m.removeClass("remove_like active");
                        } else {
                            var n = m.attr("data-url").replace("like", "unlike");
                            m.attr("data-url", n);
                            m.removeClass("add_like");
                            m.addClass("remove_like active");
                        }
                        m.removeClass("processing");
                        b(".s_like").live("click", function(p) { f(p, this) });
                    }
                });
            }

            b("#menu-mobile").load(this.menuUrl);
            b("#menu-mobile").on("click", ".bt-menu", function() {
                if (b("#menu-mobile").hasClass("open")) {
                    b("#menu-mobile").removeClass("open");
                } else {
                    b("#menu-mobile").addClass("open");
                }
            });
            b("#menu-mobile").on("click", ".dropdown", function() {
                b(this).addClass("open");
                b(this).find("ul").slideToggle();
            });
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                b("#menu-mobile").addClass("is-mobile");
            }
            b("#subscription_form").live("submit", function(g) {
                g.preventDefault();
                b.ajax({ type: "POST", url: b(this).attr("action"), data: b(this).serialize(), success: function(h) { b(".subscription_form").html(h) } });
                return false;
            });
        },
        addFancyBox: function() { b(".fancybox").fancybox() },
        submitFilterForm: function() {
            var g = b(".searchitems input:hidden");
            var d = b(".box_search .actions input");
            var f = b("#temp_form_search").append(g).append(d.clone());
            f.submit();
            b(".box_search .filter li").unbind("click");
            b("nav.menu .searchitems").off("click", "span", c.removeTag);
            b(".box_search .actions").off("change", "input:checkbox", c.submitFilterForm);
        },
        removeTag: function() {
            b(this).remove();
            c.submitFilterForm();
        },
        showHideTips: function() {
            b(".js-forms").on("click", "li", function() {
                b(".js-forms .tip").hide();
                var d = b(this).find(".tip");
                if (d.length) {
                    if (d.hasClass("error")) {
                        d.filter(".error").fadeIn("fast");
                    } else {
                        d.addClass("visible").fadeIn("fast");
                    }
                }
            });
        },
        addDiscountLetter: function() {
            b(".discount_letter").live("keydown", function() {
                var d = b(this).attr("rel") - b(this).val().length;
                if (d < 0 && e.keyCode != 46 && e.keyCode != 8) {
                    return false;
                }
                b(this).parent().find(".tip").text(d + " characters remaining");
            });
        },
        addItemForm: function(f) {
            var d = f.attr("data-prototype");
            var h = f.children().length;
            var g = d.replace(/\__name__/g, h);
            f.append(g);
            return h;
        },
        addCountdownNominee: function() {
            b(".countdown_nominee").each(function() {
                var g = b(this);
                var f = Number(g.data("time")) * 1000;
                var d = new Date(f);
                g.countdown({ until: d, format: "DHMS" });
            });
        }
    };
    b(document).ready(function() {
        c.initialize();
        b(".popup").popupWindow();
    });
    a.AW = a.AW || {};
    a.AW = c;
})(window, window.jQuery);
(function(b, c) {
    var a = {
        initialize: function(d) {
            a.addHeart();
            this.getIds(d);
        },
        openLogin: function() {
            c(".open_login").click(function(f) {
                f.preventDefault();
                var d = c(this);
                c.fancybox.showLoading();
                c.ajax({ type: "get", url: d.data("url"), success: function(g) { c.fancybox(g, { minWidth: 650, height: "autoSize", padding: 50 }) } });
            });
        },
        addVotesToSubmits: function(d) {
            for (i = 0; i < d.length; i++) {
                c("#nom_" + d[i]).text("VOTED");
                c("#nom_" + d[i]).addClass("voted");
            }
        },
        addHeartToSubmits: function(f) {
            for (i = 0; i < f.length; i++) {
                c("#like_" + f[i]).removeClass("add_like");
                c("#like_" + f[i]).addClass("remove_like active");
                var d = c("#like_" + f[i]).attr("data-vote").replace("like", "unlike");
                c("#like_" + f[i]).attr("data-url", d);
            }
        },
        getIds: function(f) {
            var g = [];
            c(".new").each(function() {
                g.push(c(this).attr("data-id"));
                c(this).removeClass("new");
            });
            if (g.length > 0) {
                var d = "submission_ids=" + g + "&user_id=" + f;
                var h = url_ajax + "?" + d;
                this.callAjaxButtons(h);
            }
        },
        callAjaxButtons: function(d) {
            c.ajax({
                url: d,
                success: function(f) {
                    a.initialize();
                    if (f.hasLike != null) {
                        a.addHeartToSubmits(f.hasLike);
                    }
                    if (f.hasVote != null) {
                        a.addHeart(f.hasVote);
                    }
                }
            });
        },
        addHeart: function() {
            c(".add-like").each(function() {
                c(this).removeClass("open_login");
                c(this).addClass("add_like s_like");
                c(this).attr("data-url", c(this).attr("data-vote"));
            });
        }
    };
    b.AWB = b.AWB || {};
    b.AWB = a;
})(window, window.jQuery);
$(document).ready(ScrollMenuMobile);
$(window).resize(ScrollMenuMobile);

function ScrollMenuMobile() { setTimeout(function() { $(".wrapper-nav").css("height", $(window).height() + "px") }, 500) }

function checkAdBlock() {
    var a = false;
    if ($.isAdblockOn === undefined) {
        a = true;
    }
    return a;
};