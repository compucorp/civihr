define([], function () {
    'use strict';

    return [
        'editableDirectiveFactory', '$timeout',
        function (editableDirectiveFactory, $timeout) {
            var linkOrg, dir;

            dir = editableDirectiveFactory({
                directiveName: 'editableTa',
                inputTpl: '<text-angular></text-angular>',
                render: function() {
                    this.parent.render.call(this);

                    this.inputEl.parent().parent().removeClass('form-inline');
                    this.inputEl.addClass('editable-ta');
                    this.inputEl.attr('ng-model','ta.$data');
                    this.inputEl.attr('ta-toolbar', this.attrs.eTaToolbar || '[["bold","italics","underline","strikeThrough","ul","ol","undo","redo","clear"]]');
                },
                save: function(){
                    this.scope.$data = this.scope.ta.$data;
                    this.parent.save.call(this);
                },
                setLocalValue: function() {
                    this.parent.setLocalValue.call(this);
                    this.scope.ta.$data = this.scope.$data;
                }
            });

            linkOrg = dir.link;

            dir.link = function (scope, el, attrs, ctrl) {
                scope.ta = {};

                return linkOrg(scope, el, attrs, ctrl);
            };

            return dir;
        }
    ];
});
