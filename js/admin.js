! function (e) {
    var t = {};

    function r(n) {
        if (t[n]) return t[n].exports;
        var i = t[n] = {
            i: n,
            l: !1,
            exports: {}
        };
        return e[n].call(i.exports, i, i.exports, r), i.l = !0, i.exports
    }
    r.m = e, r.c = t, r.d = function (e, t, n) {
        r.o(e, t) || Object.defineProperty(e, t, {
            enumerable: !0,
            get: n
        })
    }, r.r = function (e) {
        "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
            value: "Module"
        }), Object.defineProperty(e, "__esModule", {
            value: !0
        })
    }, r.t = function (e, t) {
        if (1 & t && (e = r(e)), 8 & t) return e;
        if (4 & t && "object" == typeof e && e && e.__esModule) return e;
        var n = Object.create(null);
        if (r.r(n), Object.defineProperty(n, "default", {
                enumerable: !0,
                value: e
            }), 2 & t && "string" != typeof e)
            for (var i in e) r.d(n, i, function (t) {
                return e[t]
            }.bind(null, i));
        return n
    }, r.n = function (e) {
        var t = e && e.__esModule ? function () {
            return e.default
        } : function () {
            return e
        };
        return r.d(t, "a", t), t
    }, r.o = function (e, t) {
        return Object.prototype.hasOwnProperty.call(e, t)
    }, r.p = "/js", r(r.s = 14)
}({
    14: function (e, r) {
        /**
         * @license GNU AGPL version 3 or any later version
         *
         * This program is free software: you can redistribute it and/or modify
         * it under the terms of the GNU Affero General Public License as
         * published by the Free Software Foundation, either version 3 of the
         * License, or (at your option) any later version.
         *
         * This program is distributed in the hope that it will be useful,
         * but WITHOUT ANY WARRANTY; without even the implied warranty of
         * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         * GNU Affero General Public License for more details.
         *
         * You should have received a copy of the GNU Affero General Public License
         * along with this program.  If not, see <http://www.gnu.org/licenses/>.
         */
        let n = [];
        const i = "#side-menu-message",
            a = e => {
                const r = n.get(e);
                let s, u;
                if (jQuery(r).is("[data-checkbox]")) {
                    u = jQuery(r).attr("data-name");
                    const e = jQuery('input[name="' + u + '[]"]:checked');
                    s = [], e.each((e, t) => {
                        s.push(t.value)
                    }), s = JSON.stringify(s)
                } else u = jQuery(r).attr("name"), s = jQuery(r).val();
                const o = n.length;
                "cache" === u && ++s;
                const l = {
                    success: () => {
                        OC.msg.finishedSuccess(i, t("aaochat", e + 1 + "/" + o)), e < o - 1 ? a(++e) : OC.msg.finishedSuccess(i, t("aaochat", "Saved"))
                    },
                    error: () => {
                        OC.msg.finishedError(i, t("aaochat", 'Error while saving "' + r + '"'))
                    }
                };
                jQuery(r).is("[data-personal]") ? ((e, t, r) => {
                    const n = OC.generateUrl("/apps/aaochat/aaochatSetting/valueSet");
                    $.post(n, {
                        name: e,
                        value: t
                    }, r.success).fail(r.error)
                })(u, s, l) : ((e, t, r) => {
                    OCP.AppConfig.setValue("aaochat", e, t, r)
                })(u, s, l)
            };
        jQuery(document).ready(() => {
            n = jQuery(".side-menu-setting"), jQuery("#side-menu-save").on("click", e => {
                //e.preventDefault(), OC.msg.startSaving(i), a(0)
            }), jQuery(".side-menu-display").on("click", e => {
                var t = jQuery(e.target);
                jQuery(".side-menu-display").removeClass("is-active"), t.addClass("is-active"), jQuery("#side-menu-always-displayed").val(t.attr("data-alwaysdiplayed")), jQuery("#side-menu-big-menu").val(t.attr("data-bigmenu"))
            }), jQuery(".side-menu-setting-live").on("change", e => {
                var t = jQuery(e.target),
                    r = t.attr("name"),
                    n = t.val();
                "opener" === r && (n = `url(${OC.generateUrl(`/apps/aaochat/img/${n}.svg`).replace("/index.php","")})`);
                "icon-invert-filter" !== r && "icon-opacity" !== r || (n /= 100), document.documentElement.style.setProperty("--side-menu-" + r, n)
            }), jQuery(".side-menu-toggler").on("click", e => {
                (e => {
                    jQuery(e).toggle()
                })(jQuery(e.target).attr("data-target"))
            }), jQuery("#categories-list .side-menu-setting-list").sortable({
                forcePlaceholderSize: !0,
                placeholder: "placeholder",
                stop: function (e, t) {
                    let r = [];
                    jQuery("#categories-list .side-menu-setting-list-item").each((function () {
                        r.push(jQuery(this).attr("data-id"))
                    })), r = JSON.stringify(r), jQuery('input[name="categories-order"]').val(r)
                }
            })
        })
    }
});