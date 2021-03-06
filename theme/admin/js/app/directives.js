'use strict';
/* Directives */


angular.module('myApp.directives', []).
        directive('appVersion', ['version', function(version) {
        return function(scope, elm, attrs) {
            elm.text(version);
        };
    }])
        .directive('ckEditor', function() {
    return {
        require: '?ngModel',
        link: function(scope, elm, attr, ngModel) {

            var ck = CKEDITOR.replace(elm[0],
                    {
                        toolbar_Full:
                                [
                                    {name: 'document', items: []},
                                    {name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']},
                                    {name: 'editing', items: ['Find', 'Replace', '-', 'SpellChecker', 'Scayt']},
                                    {name: 'forms', items: []},
                                    {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
                                    {name: 'paragraph', items: [
                                            'NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
                                    {name: 'links', items: []},
                                    {name: 'insert', items: ['SpecialChar']},
                                    '/',
                                    {name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
                                    {name: 'colors', items: []},
                                    {name: 'tools', items: ['Maximize']}
                                ]
                                ,
                        height: '290px',
                        width: '99%'
                    }
            );

            if (!ngModel)
                return;

            //loaded didn't seem to work, but instanceReady did
            //I added this because sometimes $render would call setData before the ckeditor was ready
            ck.on('instanceReady', function() {
                ck.setData(ngModel.$viewValue);
            });

            ck.on('pasteState', function() {
                scope.$apply(function() {
                    ngModel.$setViewValue(ck.getData());
                });
            });

            ngModel.$render = function(value) {
                ck.setData(ngModel.$viewValue);
            };
        }
    };
})



        .directive('authCheck', ['$rootScope', '$location', 'AuthService', function($root, $location, auth) {
        return {
            link: function(scope, elem, attrs, ctrl) {
                $root.$on('$routeChangeStart', function(event, currRoute, prevRoute) {
// if (!prevRoute.access.isFree && !userSrv.isLogged) {
                    if (!auth.isLogged) {

                        $.post(site.base + 'admin/account/is_authenticated')
                                .done(function(r) {
                            scope.$apply(function() {
                                if (r) {
                                    auth.isLogged = true;
                                    auth.member = r;
                                } else {
                                    auth.isLogged = false;
                                    auth.member = false;
                                    $location.path('/account/login');
                                }
                            });
                        }).fail(function() {
                            scope.$apply(function() {
                                if (!auth.isLogged) {
                                    $location.path('/account/login');
                                }
                            });
                        });
                    }
                });
            }
        };
    }])
        .directive('authMenu', ['$rootScope', '$location', 'AuthService', function($root, $location, auth) {
        return {
            templateUrl: site.base + 'theme/admin/js/partials/controls/auth/menu.html',
            replace: true,
            link: function(scope, elem, attrs, ctrl) {
                scope.auth = auth;
            }
        }
    }])
app.directive('upload', function() {
    return {
        restrict: 'A',
        templateUrl: site.base + 'theme/admin/js/partials/controls/upload.html',
        scope: {
            property: '=',
            working: '=',
            form: '=',
            doupload: '&',
            upload: '@',
            max: '@',
            thumbnail: '@',
            fieldid: '@',
            required: '&'
        },
        link: function(scope, elm, attr) {

            function dragEnterLeave(evt) {
                evt.stopPropagation();
                evt.preventDefault();
                scope.$apply(function() {
                    scope.dropText = 'Drop files here...';
                    scope.dropClass = '';
                });
            }

            function uploadComplete(evt) {
                /* This event is raised when the server send back a response */
                scope.$apply(function() {
                    var response = $.parseJSON(evt.target.responseText);
                    scope.response = response;
                    response.upload_data.forEach(function(e) {
//                        var replications = [];
//                        e.forEach(function(replication) {
//                            replications.push(replication.full_path); //                        });
                        scope.uploaded.push(e);
                    });
                    // return the uploaded fiels name to the model
                    scope.property = JSON.stringify(scope.uploaded);
                    scope.files = [];
                    scope.working = false;
                });
                //                    alert(evt.target.responseText)
            }
            function uploadFailed(evt) {
                alert("There was an error attempting to upload the file.");
                scope.$apply(function() {
                    scope.working = false;
                });
            }
            function uploadCanceled(evt) {
                scope.$apply(function() {
                    scope.progressVisible = false;
                    scope.working = false;
                });
                alert("The upload has been canceled by the user or the browser dropped the connection.")
            }

            function uploadProgress(evt) {
                scope.$apply(function() {
                    if (evt.lengthComputable) {
                        scope.progress = Math.round(evt.loaded * 100 / evt.total);
                    } else {
                        scope.progress = 'unable to compute'
                    }
                });
            }

            scope.$watch('property', function(property) {
                if (property) {

                    /** UPLOAD **/
//============== DRAG & DROP =============
// source for drag&drop: http://www.webappers.com/2011/09/28/drag-drop-file-upload-with-html5-javascript/
                    var dropbox = elm.find('.dropbox')[0];
                    scope.dropText = 'Drop files here...';
                    if (scope.property) {
                        scope.uploaded = JSON.parse(scope.property)
                    } else {
                        scope.property = [];
                        scope.uploaded = []; // files that successfully uploaded
                    }
                    ;
                    scope.files = []; // files attached in browser but still not uploaded         scope.errors = [];
                    // init event handlers

                    dropbox.addEventListener("dragenter", dragEnterLeave, false);
                    dropbox.addEventListener("dragleave", dragEnterLeave, false);
                    dropbox.addEventListener("dragover", function(evt) {
                        evt.stopPropagation();
                        evt.preventDefault();
                        var ok = evt.dataTransfer && evt.dataTransfer.types && evt.dataTransfer.types.indexOf('Files') >= 0;
                        scope.$apply(function() {
                            scope.dropText = ok ? 'Drop files here...' : 'Only files are allowed!';
                            scope.dropClass = ok ? 'over' : 'not-available';
                        });
                    }, false);
                    dropbox.addEventListener("drop", function(evt) {
//console.log('drop evt:', JSON.parse(JSON.stringify(evt.dataTransfer)))         evt.stopPropagation();
                        evt.preventDefault();
                        scope.$apply(function() {
                            scope.dropText = 'Drop files here...';
                            scope.dropClass = '';
                        });
                        var files = evt.dataTransfer.files;
                        if (files.length > 0) {
                            scope.$apply(function() {
                                for (var i = 0; i < files.length; i++) {
                                    if ((scope.files.length + scope.uploaded.length) == scope.max) {
                                        break;
                                    }
                                    scope.files.push(files[i]);
                                }
                                scope.progressVisible = false;
                                scope.progress = 0;
                            });
                        }
                    }, false);
                    //============== DRAG & DROP =============

                    scope.removeFile = function(el, removeOnServer) {
                        var removeOnServer = removeOnServer || false;
                        if (removeOnServer) {
                            $.post(site.base + 'admin/uploads/remove', {file: JSON.stringify(el.file)}, function(r) {
                                scope.$apply(function(r) {

                                    var oldFiles = scope.uploaded;
                                    scope.uploaded = [];
                                    oldFiles.forEach(function(e) {
                                        if (el.file[0].full_path !== e[0].full_path) {
                                            scope.uploaded.push(e);
                                        }
                                    });
                                    scope.property = JSON.stringify(scope.uploaded);
                                });
                            });
                        } else {
                            var oldFiles = scope.files;
                            scope.files = [];
                            oldFiles.forEach(function(e) {
                                if (el.file.name !== e.name) {
                                    scope.files.push(e);
                                }
                            });
                        }
                    };
                    scope.setFiles = function(element) {
                        scope.$apply(function(scope) {
//console.log('files:', element.files);
// Turn the FileList object into an Array
                            scope.files = []
                            for (var i = 0; i < element.files.length; i++) {
                                scope.files.push(element.files[i])
                            }
                            scope.progressVisible = false
                        });
                    };
                    scope.uploadFile = function() {
                        scope.working = true;
                        var fd = new FormData();
                        var c = 0;
                        for (var i in scope.files) {
                            fd.append("file" + (++c), scope.files[i]);
                        }
                        ;
                        //console.log(fd);
                        var xhr = new XMLHttpRequest();
                        xhr.upload.addEventListener("progress", uploadProgress, false);
                        xhr.addEventListener("load", uploadComplete, false);
                        xhr.addEventListener("error", uploadFailed, false);
                        xhr.addEventListener("abort", uploadCanceled, false);
                        xhr.open("POST", scope.upload);
                        scope.progressVisible = true;
                        xhr.send(fd);
                    };
                    /** UPLOAD **/

                }
            })
        }
    }
});


