function isiPhone(){return-1!=navigator.platform.indexOf("iPhone")||-1!=navigator.platform.indexOf("iPod")}isiPhone();var fn_template='<img class="{class_name}" src="{url}" />';isiPhone()||(!function(n){function e(e,t,o,i,a,r){var s=n(t).data("img-src");void 0!=s&&s>""&&(e=n(e),text=n(t).val(),a=void 0!=a?a:!0,r=r||(a?"chose-image":"chose-image-small"),r=i?r+" rtl":r,o=o.replace("{url}",s).replace("{class_name}",r).replace("{text}",text),e.empty(),i&&a?e.append(o):e.prepend(o))}function t(e){for(var t=[],o=n(e.form_field).find("option:selected")||[],i=e.is_multiple?n(e.container).find(".chosen-choices span"):n(e.container).find(".chosen-single span"),a=0;a<o.length;a++)for(var r=0;r<i.length;r++)n(i[r]).text()==n(o[a]).text()&&t.push({span:i[r],option:o[a]});return t}var o=n.fn.chosen;n.fn.extend({chosen:function(i){i=i||{};o.apply(this,arguments);this.each(function(){var o=n(this),a=o.data("chosen");o.on("chosen:hiding_dropdown",function(n,o){for(var a=t(o.chosen),r=o.chosen.is_rtl,s=o.chosen.is_multiple,c=i.html_template||(r&&s?"{text}"+fn_template:fn_template+"{text}"),h=0;h<a.length;h++)e(a[h].span,a[h].option,c,r,s)}),o.on("chosen:showing_dropdown",function(t,o){for(var o=o.chosen,a=o.form_field.options||[],r=o.is_rtl,s=i.html_template||r?"{text}"+fn_template:fn_template+"{text}",c=n(o.container).find(".chosen-drop ul li:not(:has(img))"),h=0;h<c.length;h++){var d=c[h],l=n(a[h]),f=n(d).attr("data-option-array-index");f&&(l=a[o.results_data[f].options_index]),e(d,l,s,r,!0,"chose-image-list")}}),o.on("chosen:ready",function(n,e){o.trigger("chosen:hiding_dropdown",e)}),o.on("chosen:filter",function(n,e){o.trigger("chosen:showing_dropdown",{chosen:e.chosen})}),o.trigger("chosen:hiding_dropdown",{chosen:a})})}})}(jQuery),function(n){n.fn.chosenImage=function(e){return this.each(function(){function t(n){return n?{"background-image":"url("+n+")","background-repeat":"no-repeat"}:{"background-image":"none"}}var o=n(this),i={};o.find("option").filter(function(){return n(this).text()}).each(function(e){var t=n(this).attr("data-img-src");i[e]=t}),o.chosen(e);var a=o.attr("id");a+="_chzn";var r="#"+a,s=n(r).addClass("chznImage-container");s.find(".chzn-results li").each(function(e){n(this).css(t(i[e]))}),o.change(function(){var n=o.find("option:selected").attr("data-img-src")?o.find("option:selected").attr("data-img-src"):"";s.find(".chzn-single span").css(t(n))}),o.trigger("change")})}}(jQuery));