<?php
/**
 * User: Mani Wang
 * Date: 8/13/15
 * Time: 9:16 PM
 * Email: mani@forone.co
 */

namespace Forone\Admin\Providers;


use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Form;

class QiniuUploadProvider extends ServiceProvider
{

    static $single_inited;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->singleFileUpload();
        $this->multiFilesUpload();
    }

    private function singleFileUpload()
    {
        $handler = function ($name, $label, $percent = 0.5,$platform="qiniu") {
            $value = ForoneFormServiceProvider::parseValue($this->model, $name);
            $url = $value ? config('forone.qiniu.host') . $value : '/vendor/forone/images/upload_add.png';
            $js = View::make('forone::upload.upload')->with(['name'=>$name])->render();
            if(!QiniuUploadProvider::$single_inited){
                $js = View::make('forone::upload.upload_js')->render() . $js;
                QiniuUploadProvider::$single_inited = true;
            }
            return $js.'<div class="form-group col-sm-' . ($percent * 12) . '">
                        ' . Form::form_label($label) . '
                        <div class="col-sm-9">
                            <input id="' . $name . '" type="hidden" name="' . $name . '" type="text" value="' . $value . '">
                            <img style="width:58px;height:58px;cursor:pointer;" id="' . $name . '_img" src="' . $url . '">
                        </div>
                    </div>';
        };
        Form::macro('single_file_upload', $handler);
    }

    private function multiFilesUpload()
    {
        Form::macro('multi_file_upload', function ($name, $label, $with_description=true, $percent=0.5,$platform="qiniu") {

            $value = ForoneFormServiceProvider::parseValue($this->model, $name);
            //dd($value);
            $url = '/vendor/forone/images/upload_add.png';
            $uploaded_items = '';
            if (is_array($value)) {
                //$items = explode('|', $value);
                foreach ($value as $item) {
                    $attach_id=$item['id'];
                    $attach_url=$item['attach_url'];
                    $attach_name=$item['attach_name'];
                    //$details = $item['attach_url'];
                    $idvalue = rand().'';
                    $div = '<div id="'.$idvalue.'div" style="float:left;width:68px;margin-right: 20px">';
                    if(preg_match("/.pdf/", $attach_url)){
                        $img = '<img onclick="removeMultiUploadItem(\'' . $idvalue . 'div\',\''.$name.'\')" style="width: 68px; height: 68px;cursor:pointer"
                        src="/vendor/forone/images/upload.png">';
                    }else{
                        $img = '<img onclick="removeMultiUploadItem(\'' . $idvalue . 'div\',\''.$name.'\')" style="width: 68px; height: 68px;cursor:pointer"
                        src="'.$attach_url.'?imageView2/1/w/68/h/68">';
                    }

                    $uploaded_items .= $div . $img;
                    $v = '';
                    if (isset($item['attach_name'])) {
                        $v = "value='$attach_name'";
                    }
                    $uploaded_items.='<input type="hidden" name="'.$name.'['.$attach_id.'][attach_url]" value="'.$attach_url.'">';
                    $uploaded_items.='<input type="hidden" name="'.$name.'['.$attach_id.'][id]" value="'.$attach_id.'">';
                    $uploaded_items.='<input  name="'.$name.'['.$attach_id.'][env_type]" value="'.$item["env_type"].'" placeholder="0:pc 1:h5" style="width: 68px;float: left">';
                    $uploaded_items.='<input  name="'.$name.'['.$attach_id.'][oid]" value="'.$item["oid"].'" placeholder="排序" style="width: 68px;float: left">';
                    $uploaded_items .= '<input '.$v.'  onkeyup="fillMultiUploadInput(\''.$name.'\')" name="'.$name.'['.$attach_id.'][attach_name]" style="width: 68px;float: left" placeholder="附件名称"></div>';
                }
            }

            $js = View::make('forone::upload.upload')->with(['multi'=>true,'name'=>$name, 'with_description'=>$with_description])->render();
            if(!QiniuUploadProvider::$single_inited){
                $js = View::make('forone::upload.upload_js')->render() . $js;
                QiniuUploadProvider::$single_inited = true;
            }

            return $js.'<div class="form-group col-sm-' . ($percent * 12) . '">
                        ' . Form::form_label($label) . '
                        <div class="col-sm-9">
                            <input id="'.$name.'" type="hidden" name="' . $name . '" type="text" value="">
                            <img style="width:58px;height:58px;cursor:pointer;float:left;margin-right:20px;" id="'.$name.'_img" src="'.$url.'">
                            <label id="'.$name.'_label"></label>
                            <div id="'.$name.'_div">'.$uploaded_items.'</div>
                        </div>
                    </div>';
        });
    }
}