define([], function () {
    'use strict';

    return [
        '$delegate',
        function ($delegate) {
            return function (overwrites) {
                var dirObj = $delegate(overwrites);

                dirObj.compile = function (tEl) {
                    var linkEl;
                    var ngHrefAttr = tEl[0].attributes.getNamedItem('ng-href');

                    tEl.append('<i class="fa fa-pencil" />');

                    if (ngHrefAttr) {
                        linkEl = angular.element('<a />');
                        linkEl.text('Follow link');
                        linkEl.attr(ngHrefAttr.nodeName, ngHrefAttr.value);
                        linkEl.attr('ng-click', '$event.stopPropagation();');
                        linkEl.addClass('editable-link');

                        tEl.append(linkEl);
                        tEl.addClass('editable-with-link');
                    }

                    return {
                        post: dirObj.link
                    };
                };

                return dirObj;
            };
        }
    ];
});
