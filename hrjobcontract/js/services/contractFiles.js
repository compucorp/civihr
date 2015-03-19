define(['services/services',
        'services/utils'], function (services) {

    services.factory('ContractFilesService', ['$resource', 'settings', '$q', 'UtilsService','FileUploader', '$log',
        function ($resource, settings, $q, UtilsService, FileUploader, $log) {
            $log.debug('Service: ContractFilesService');

            var ContractFiles = $resource(settings.pathFile+':action');
                FileUploader.prototype.queueDelete = [];

            return {
                delete: function(fileId, entityId, entityTable){

                    if ((!fileId || typeof +fileId !== 'number') ||
                        (!entityId || typeof +entityId !== 'number') ||
                        (!entityTable || typeof entityTable !== 'string')) {
                        return null;
                    }

                    var deffered = $q.defer();

                    ContractFiles.save({
                        action: 'delete',
                        entityTable: entityTable,
                        entityID: entityId,
                        fileID: fileId
                    }, null, function(data){
                        if (data.values && !+data.values[0].result) {
                            data.is_error = 1;
                        }

                        if (UtilsService.errorHandler(data,'Unable to delete file',deffered)) {
                            return
                        }

                        deffered.resolve(data.values[0]);
                    },function(){
                        deffered.reject('Unable to delete file');
                    });

                    return deffered.promise;
                },
                get: function(entityId, entityTable){

                    if ((!entityId || typeof +entityId !== 'number') ||
                        (!entityTable || typeof entityTable !== 'string')) {
                        return null;
                    }

                    var deffered = $q.defer();

                    ContractFiles.get({
                        action: 'list',
                        entityTable: entityTable,
                        entityID: entityId
                    }, function(data){

                        if (UtilsService.errorHandler(data,'Unable to fetch files',deffered)) {
                            return
                        }

                        deffered.resolve(data.values);
                    },function(){
                        deffered.reject('Unable to fetch files');
                    });

                    return deffered.promise;
                },
                uploader: function(entityTable, queueLimit){

                    if (!entityTable || typeof entityTable !== 'string') {
                        return null;
                    }

                    var uploaderSettings = {
                        url: settings.pathFile + 'upload',
                        formData: [
                            {
                                entityTable: entityTable
                            }
                        ]
                    }

                    if (queueLimit && typeof queueLimit === 'number') {
                        uploaderSettings.queueLimit = queueLimit;
                    }

                    return new FileUploader(uploaderSettings);
                },
                upload: function(uploaderInstance, revisionId) {

                    if (!uploaderInstance || typeof uploaderInstance !== 'object' ||
                        !revisionId || typeof +revisionId !== 'number') {
                        return null;
                    }

                    var deffered = $q.defer(), results = [];

                    uploaderInstance.onBeforeUploadItem = function(item){
                        item.formData.push({
                            entityID: revisionId
                        });
                    };

                    uploaderInstance.onCompleteItem = function(item, response){
                        results.push(response);
                    };

                    uploaderInstance.onErrorItem = function(item, response, status, headers){
                        deffered.reject('Could not upload file: '+item.file.name);
                        $log.error(' ===== Item Error: ' + status + ' ======');
                        $log.error(' =====  - item ======');
                        $log.error(item);
                        $log.error(' =====  - response ======');
                        $log.error(response);
                        $log.error(' =====  - headers ======');
                        $log.error(headers);
                    };

                    uploaderInstance.onCompleteAll = function(){
                        deffered.resolve(results);
                    };

                    uploaderInstance.uploadAll();

                    return deffered.promise
                }
            }

    }]);

});