define([], function () {
    'use strict';

    return [
        'editableDirectiveFactory', '$timeout',
        function (editableDirectiveFactory) {
            var linkOrg, dir;

            function rename(tag, el) {
                var attrs = el[0].attributes;
                var newEl = angular.element('<' + tag + '/>');

                newEl.html(el.html());

                for (var i = 0; i < attrs.length; ++i) {
                    newEl.attr(attrs.item(i).nodeName, attrs.item(i).value);
                }

                return newEl;
            };

            dir = editableDirectiveFactory({
                directiveName: 'editableUiSelect',
                inputTpl: '<ui-select></ui-select>',
                uiSelectMatch: null,
                uiSelectChoices: null,
                render: function () {
                    this.parent.render.call(this);

                    this.inputEl.attr('ng-model','select.$data');
                    this.inputEl.append(rename('ui-select-match', this.parent.uiSelectMatch));
                    this.inputEl.append(rename('ui-select-choices', this.parent.uiSelectChoices));

                },
                save: function(){
                    this.scope.$data = this.scope.select.$data;
                    this.parent.save.call(this);
                },
                setLocalValue: function() {
                    this.parent.setLocalValue.call(this);
                    this.scope.select.$data = this.scope.$data;
                }

            });

            linkOrg = dir.link;

            dir.link = function (scope, el, attrs, ctrl) {
                var matchEl = el.find('editable-ui-select-match');
                var choicesEl = el.find('editable-ui-select-choices');

                ctrl[0].uiSelectMatch = matchEl.clone();
                ctrl[0].uiSelectChoices = choicesEl.clone();

                matchEl.remove();
                choicesEl.remove();

                scope.select = {};

                return linkOrg(scope, el, attrs, ctrl);
            };

            return dir;
        }
    ];
});
