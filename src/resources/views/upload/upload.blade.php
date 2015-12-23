<script type='text/javascript'>
    init.push(function(){

        Qiniu.uploader({
            browse_button: "{{$name}}_img",
            uptoken_url: '{{ route("admin.qiniu.upload-token") }}',
            unique_names: true,
            domain: '{{config('forone.qiniu.host')}}',
            max_file_size: '100mb',
            flash_swf_url: '/vendor/forone/components/qiniu/plupload/Moxie.swf',
            max_retries: 3,
            chunk_size: '4mb',
            auto_start: true,
            init: {
                'FilesAdded': function(up, files) {
                    //console.log(files);
                    plupload.each(files, function(file) {
                        @if(isset($multi))
                        var reader = new FileReader();
                        reader.onload = function(e){
                            //console.log(file);
                            //console.log(e);
                            var matches = file.name.match(/\.([^.]+)$/), ext = "part";
                            if (matches) {
                                ext = matches[1];
                            }
                            var newFileName=file.id+ '.' + ext.toLowerCase();
                            var name = "{{$name}}";
                            name=name+"["+file.id+"]";
                            var item = '<div id="'+file.id+'div" style="float:left;width:68px;margin-right: 20px">' +
                                    '<img ' +
                                    'onclick="removeMultiUploadItem(this)" ' +
                                    'id="'+file.id+'" ' +
                                    'style="width: 68px; height: 68px;cursor:pointer" ' +
                                    'src="'+ e.target.result+'">' +
                                    '<img id="'+file.id+'loading" src="/vendor/forone/components/qiniu/loading.gif">' +
                                    '<input  name="'+name+'[attach_url]"  type="hidden" value="'+"{{config('forone.qiniu.host')}}" + newFileName+'">';
                            @if(isset($with_description) && $with_description)
                                item+= '<input  name="'+name+'[env_type]"  placeholder="0:pc 1:h5" style="width: 68px;float: left">'
                                    +'<input  name="'+name+'[oid]"  placeholder="排序" style="width: 68px;float: left">'
                                    +'<input type="text" onkeyup="fillMultiUploadInput('+name+')" name="'+name+'[attach_name]" style="width: 68px;float: left" placeholder="附件名称">';
                            @else
                                item+='</div>';
                            @endif
                            $("#{{$name}}_div").append(item);
                        }
                        reader.readAsDataURL(file.getNative());
                        @endif
                    });
                },
                'UploadProgress': function(up, file) {
                    console.log(up);
                },
                'FileUploaded': function(up, file, info) {
                    var domain = up.getOption('domain');
                    console.log(info);
                    var res = $.parseJSON(info);
                    var sourceLink = domain + res.key;
                    @if(!isset($multi))
                    $("#{{$name}}_img").attr("src",sourceLink+'?imageView2/1/w/68/h/68');
                    $("#{{$name}}").attr("value",sourceLink);
                    @else
                        $("#"+file.id).attr("src",sourceLink+'?imageView2/1/w/68/h/68');
                    $("#"+file.id+"loading").remove();
                    fillMultiUploadInput(name);
                    @endif
                },
                'Error': function(up, err, errTip) {
                },
                'UploadComplete': function() {
                }
            }
        });
    });
</script>