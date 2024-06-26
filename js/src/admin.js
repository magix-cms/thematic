var thematic = (function ($, undefined) {
    'use strict';
    function parentPages(){
        if($('#parent_id').val() !== ''){
            var id = $('#parent_id').val();
            var cus = $('#filter-pages').find('li[data-value="'+id+'"]');
            if(!cus.length) {
                $('#parent').bootstrapSelect('clear');
            } else {
                var cu = $(cus[0]);
                $('#parent').bootstrapSelect('select',cu);
            }
        }
        $('#parent_id').on('focusout',function(){
            var id = $(this).val();
            if(id !== '') {
                var cus = $('#filter-pages').find('li[data-value="'+id+'"]');
                if(!cus.length) {
                    $('#parent').bootstrapSelect('clear');
                    $('#parent_id').val('');
                } else {
                    var cu = $(cus[0]);
                    $('#parent').bootstrapSelect('select',cu);
                }
            }
        });
    }
    function initGen(fd,controller,globalForm,tableForm){
        var progressBar = new ProgressBar({loader: {type:'text', icon:'etc', class: ''}});
        $.jmRequest({
            handler: "ajax",
            url: $('#add_img_thematic').attr('action'),
            method: 'POST',
            data:  fd,
            processData: false,
            contentType: false,
            beforeSend: function () {
                progressBar.init();
            },
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                //Upload progress
                xhr.oldResponse = '';
                // Generation progress
                xhr.upload.addEventListener("progress", function(e){
                    if (e.lengthComputable) {
                        let percentComplete = (e.loaded / e.total);
                        //Do something with upload progress
                        // let total = Math.round((e.total / (1024*1024))*10)/10;
                        // let loaded = Math.round((e.loaded / (1024*1024))*10)/10;
                        let options = {
                            progress: percentComplete*30,
                            state: 'upload complete at '+Math.round(percentComplete*100)+'%',
                        }
                        progressBar.update(options);
                        if(percentComplete === 100) {
                            progressBar.init({state: ''});
                        }
                    }
                });
                xhr.addEventListener("progress", function(e){
                    if(!(xhr.readyState === 4 && xhr.status === 200)) {
                        let new_response = xhr.responseText.substring(xhr.oldResponse.length);
                        if(new_response.trim() !== '') {
                            let result = JSON.parse(new_response.trim());
                            let options = {
                                progress: result.progress,
                                state: result.message,
                            }
                            if(result.loader !== null) {
                                options['loader'] = result.loader;
                            }
                            /*if(result.rendering) {
                                options['loader'] = {type: 'fa', icon: 'cog', anim: 'spin', class: 'fa fa-cog fa-spin fa-fw'};
                            }*/
                            progressBar.update(options);
                            xhr.oldResponse = xhr.responseText;
                        }
                    }
                }, false);
                return xhr;
            },
            dataFilter: function (response) {
                var responses = response.split('{');
                response = '{'+responses.pop();
                return response;
            },
            error: function (xhr, ajaxOptions, thrownError) {
                progressBar.updateState('danger');
                console.log(xhr);
                console.log(ajaxOptions);
                console.log(thrownError);
            },
            success: function (d) {
                if(d.status == 'success') {
                    progressBar.updateState('success');
                    progressBar.update({state: d.message+' <span class="fa fa-check"></span>',loader: false});

                    $.jmRequest({
                        handler: "ajax",
                        url: controller+'&action=getImages',
                        method: 'get',
                        success: function (d) {
                            $('.block-img').empty();
                            $('.block-img').html(d.result);
                            globalForm.initModals();
                            tableForm.run();
                            $('.block-img').find('.img-zoom').fancybox();
                            initDefaultImg(controller);
                            initSortable(controller);
                        }
                    });
                }
                else {
                    switch (d.error_code) {
                        case 'access_denied':
                            progressBar.updateState('danger');
                            progressBar.update({state: d.message+' <span class="fa fa-ban"></span>',loader: false});
                            break;
                        case 'error_data':
                            progressBar.updateState('warning');
                            progressBar.update({state: '<span class="fa fa-warning"></span> '+d.message,loader: false});
                            break;
                    }
                }
            },
            complete: function () {
                progressBar.update({progress: 100});
                progressBar.initHide();
                //progressBar.element.parent().next().removeClass('hide');
            }
        });
    }

    function initDropZone() {
        var dropZoneId = "drop-zone";
        var buttonId = "clickHere";
        var mouseOverClass = "mouse-over";
        var btnSend = $("#" + dropZoneId).find('button[type="submit"]');
        //console.log(btnSend);
        var dropZone = $("#" + dropZoneId);
        var ooleft = dropZone.offset().left;
        var ooright = dropZone.outerWidth() + ooleft;
        var ootop = dropZone.offset().top;
        var oobottom = dropZone.outerHeight() + ootop;
        var inputFile = dropZone.find('input[type="file"]');
        document.getElementById(dropZoneId).addEventListener("dragover", function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.addClass(mouseOverClass);
            var x = e.pageX;
            var y = e.pageY;

            if (!(x < ooleft || x > ooright || y < ootop || y > oobottom)) {
                inputFile.offset({ top: y - 15, left: x - 100 });
            } else {
                inputFile.offset({ top: -400, left: -400 });
            }

        }, true);

        if (buttonId !== "") {
            var clickZone = $("#" + buttonId);

            var oleft = clickZone.offset().left;
            var oright = clickZone.outerWidth() + oleft;
            var otop = clickZone.offset().top;
            var obottom = clickZone.outerHeight() + otop;

            $("#" + buttonId).mousemove(function (e) {
                var x = e.pageX;
                var y = e.pageY;
                if (!(x < oleft || x > oright || y < otop || y > obottom)) {
                    inputFile.offset({ top: y - 15, left: x - 160 });
                } else {
                    inputFile.offset({ top: -400, left: -400 });
                }
            });
        }

        $("#" + dropZoneId).find('input[type="file"]').change(function(){
            var inputVal = $(this).val();
            if(inputVal === '') {
                $(btnSend).prop('disabled',true);
            } else {
                $(btnSend).prop('disabled',false);
            }
        });

        document.getElementById(dropZoneId).addEventListener("drop", function (e) {
            $("#" + dropZoneId).removeClass(mouseOverClass);
        }, true);
    }

    function initDefaultImg(controller) {
        $('.make_default').off().on('click', function(){
            var self = this,
                dflt = $('.default.in'),
                id = $(this).data('id');

            $('.default').removeClass('in');
            $('.make-default').removeClass('hide');
            $(this).parent().addClass('hide').prev().addClass('in').find('.fa').attr('class','fa fa-spinner fa-pulse');

            $.jmRequest({
                handler: "ajax",
                url: controller+'&action=setImgDefault',
                data: {id_img: id},
                method: 'post',
                success: function (d) {
                    if(!d.status) {
                        $(self).parent().removeClass('hide').prev().removeClass('in');
                        dflt.addClass('in').next().addClass('hide');
                    }

                    $(self).parent().prev().find('.fa').attr('class','fa fa-check text-success');
                }
            });
            return false;
        });

        $('#gallery-pages .sortable').off().on('change',function(){
            var dflt = $('.default.in');
            if(!dflt.length) {
                $.jmRequest({
                    handler: "ajax",
                    url: controller+'&action=getImgDefault',
                    method: 'get',
                    success: function (d) {
                        $('#image_'+d).find('.default').addClass('in').next().addClass('hide');
                    }
                });
            }
        });
    }

    function initSortable(controller) {
        $( ".row.sortable" ).sortable({
            items: "> div",
            cursor: "move",
            update: function(){
                var serial = $( ".sortable" ).sortable('serialize');
                $.jmRequest({
                    handler: "ajax",
                    url: controller+'&action=orderImages',
                    method: 'POST',
                    data : serial,
                    success:function(e){
                        $.jmRequest.initbox(e,{
                                display: false
                            }
                        );
                    }
                });
            }
        });
        $( ".row.sortable" ).disableSelection();
    }

    return {
        run: function(controller){
            parentPages();
            $( ".ui-sortable" ).sortable({
                items: "> tr",
                cursor: "move",
                axis: "y",
                update: function(){
                    var serial = $( ".ui-sortable" ).sortable('serialize');
                    $.jmRequest({
                        handler: "ajax",
                        url: controller+'&action=order',
                        method: 'POST',
                        data : serial,
                        success:function(e){
                            $.jmRequest.initbox(e,{
                                    display: false
                                }
                            );
                        }
                    });
                }
            });
            $( ".ui-sortable" ).disableSelection();
        },
        runAdd : function(){
            if($('#parent_id').val() !== ''){
                var id = $('#parent_id').val();
                var cus = $('#filter-pages').find('li[data-value="'+id+'"]');
                if(!cus.length) {
                    $('#parent').bootstrapSelect('clear');
                } else {
                    var cu = $(cus[0]);
                    $('#parent').bootstrapSelect('select',cu);
                }
            }
            $('#parent_id').on('focusout',function(){
                var id = $(this).val();
                if(id !== '') {
                    var cus = $('#filter-pages').find('li[data-value="'+id+'"]');
                    if(!cus.length) {
                        $('#parent').bootstrapSelect('clear');
                        $('#parent_id').val('');
                    } else {
                        var cu = $(cus[0]);
                        $('#parent').bootstrapSelect('select',cu);
                    }
                }
            });
        },
        runEdit: function(globalForm,tableForm,controller){
            parentPages();
            $('.progress').hide();
            $('.form-gen').on('submit', function(e) {
                e.preventDefault();
                var fd = new FormData(this);
                initGen(fd,controller,globalForm,tableForm);
                return false;
            });
            //$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            //if($(e.target).attr('href') === '#images') {
            //initDropZone();
            initDefaultImg(controller);
            initSortable(controller);
            //}
            //});
        }
    };
})(jQuery);