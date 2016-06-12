<?php
/**
 * User: Mani Wang
 * Date: 8/13/15
 * Time: 9:16 PM
 * Email: mani@forone.co
 */

namespace Forone\Admin\Providers;

use Form;
use Forone\Admin\Services\PagerPresenter;
use Html;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\ServiceProvider;

class ForoneHtmlServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->groupLabel();
        $this->panelStart();
        $this->panelEnd();
        $this->modalStart();
        $this->modalEnd();
        $this->modalButton();
        $this->json();
        $this->datagridHeader();
        $this->dataGrid();
        $this->datagridFooter();
    }

    public static function parseValue($model, $name)
    {
        $arr = explode('.', $name);
        if (sizeof($arr) == 2) {
            return $model && (!is_array($model) || array_key_exists($arr[0], $model)) ? $model[$arr[0]][$arr[1]] : '';
        } else {
            return $model && (!is_array($model) || array_key_exists($name, $model)) ? $model[$name] : '';
        }
    }

    private function dataGrid()
    {
        Html::macro('datagrid', function ($data) {

            $html = '<table class="table m-b-none" data-sort="false" ui-jp="footable">';
            $columns = $data['columns'];
            $items = $data['items'];
            $page = isset($data['page'])?$data['page']:'';
            $heads = [];
            $widths = [];
            $fields = [];
            $functions = [];

            // build table head
            $html .= '<thead><tr>';
            foreach ($columns as $column) {
                array_push($heads, $column[0]); // title
                array_push($fields, $column[1]); // fields
                $size = sizeof($column);
                switch ($size) {
                    case 2:
                        array_push($widths, 0); // width
                        break;
                    case 3:
                        if (is_int($column[2])) {
                            array_push($widths, $column[2]);
                        } else {
                            array_push($widths, 0);
                            $functions[$column[1]] = $column[2];
                        }
                        break;
                    case 4:
                        array_push($widths, $column[2]);
                        $functions[$column[1]] = $column[3];
                        break;
                }
            }

            foreach ($heads as $head) {
                $index = array_search($head, $heads);
                $class = '';
                $dataToggle = '';
                if ($index == 0) {
                    $first = 'footable-first-column ';
                    $dataToggle = 'data-toggle="true"';
                    $class .= $first;
                }
                if ($index == sizeof($heads)) {
                    $class .= 'footable-last-column ';
                }
                if ($index <= 1) {
                    $class .= 'footable-visible ';
                } else if ($index < 4) {
                    $dataToggle .= ' data-hide="phone"';
                } else {
                    $dataToggle .= ' data-hide="tablet,phone"';
                }

                if ($widths[$index]) {
                    $dataToggle .= ' style="width:' . $widths[$index] . 'px"';
                }

                $item = '<th ' . $dataToggle . ' class="' . $class . '" >' . $head . '</th>';
                $html .= $item;
            }
            $html .= '</tr></thead>';

            $html .= '<tbody>';
            if ($items) {
                foreach ($items as $item) {
                    $html .= '<tr>';
                    foreach ($fields as $field) {
                        $index = array_search($field, $fields);
                        $html .= $widths[$index] ? '<td style="width: ' . $widths[$index] . 'px">' : '<td>';
                        if ($field == 'buttons') {
                            $buttons = $functions[$field]($item);
                            foreach ($buttons as $button) {
                                $size = sizeof($button);
                                if ($size == 1) {
                                    $value = $button[0];
                                    if ($value == '禁用') {
                                        $html .= Form::form_button([
                                            'name'  => $value,
                                            'id'    => $item->id,
                                            'class' => 'bg-warning'
                                        ], ['enabled' => false]);
                                    } else if ($value == '启用') {
                                        $html .= Form::form_button([
                                            'name'  => $value,
                                            'id'    => $item->id,
                                            'class' => 'btn-success'
                                        ], ['enabled' => true]);
                                    } else if ($value == '查看') {
                                        $html .= '<a href="' . $this->url->current() . '/' . $item['id'] . '">
                                                    <button class="btn">查看</button></a>';
                                    } else if ($value == '编辑' || $value == '查看详情') {
                                        $html .= '<a href="' . $this->url->current() . '/' . $item['id'] . '/edit">
                                                    <button class="btn">'.$value.'</button></a>';
                                    }  else if ($value == '查看/编辑') {
                                        $html .= '<a href="' . $this->url->current() . '/' . $item['id'] . '/edit">
                                                    <button class="btn">查看/编辑</button></a>';
                                    } else if ($value == '删除'){
                                        $html .= Form::form_delete($value,$this->url->current() . '/' . $item['id']);
                                    }
                                } else {
                                    $getButton = sizeof($button) > 2 ? true : false;
                                    $config = $getButton ? $button : $button[0];
                                    $data = $getButton ? [] : $button[1];
                                    if (is_string($data) && !empty($item['id'])) {
                                        //一般link
                                        if(strripos($data, '#') !== 0){
                                            $html .= '<a href="' . $data . '" style="margin-left:5px;"><button  class="btn waves-effect" >' . $config . '</button></a>';
                                        }elseif($data == '#modal'){//#modal
                                            $html .= Form::modal_button($config, $data, $item);
                                        }elseif($data == '#alert'){//#alert
                                            $html .= '<a href="' . $data . '" style="margin-left:5px;"><button  onclick="fillAlert(\'' . $item['id'] . '\')"  class="btn waves-effect" >' . $config . '</button></a>';
                                        }
                                    } else {
                                        if (array_key_exists('method', $config) && $config['method'] == 'GET') {
                                            $uri = array_key_exists('uri', $config) ? $config['uri'] : '';
                                            $params = array_key_exists('params', $config) ? $config['params'] : '';
                                            if ($params) {
                                                $params = explode(',', $params);
                                                $query = [];
                                                foreach ($params as $key) {
                                                    $query[$key] = $item[$key];
                                                }
                                                $uri .= '?' . http_build_query($query);
                                                $config['uri'] = $uri;
                                            }
                                        } else {
                                            $config['id'] = $item->id;
                                        }
                                        $html .= Form::form_button($config, $data);
                                    }
                                }
                            }
                        }elseif($field == 'label'){
                            $labelVal=$functions[$field]($item);
                            $html.=Form::label($field,$labelVal);
                        }elseif($field == 'imageShow'){
                            $labelVal=$functions[$field]($item);
                            $picName=isset($labelVal['name'])?$labelVal['name']:'';
                            $url=$labelVal['src'];
                            $html.=Form::image($url,$picName,$labelVal);
                        }else {
                            if (array_key_exists($field, $functions)) {
                                if(!is_object($item)){
                                    $value = $functions[$field]($item[$field]);
                                }else{
                                    $value = $functions[$field]($item->$field);
                                }
                            } else {
                                $arr = explode('.', $field);
                                if (sizeof($arr) == 2) {
                                    $value = $item[$arr[0]][$arr[1]];
                                } else {
                                    if(is_object($item))
                                        $value = $item->$field;
                                    else{
                                        $value = $item[$field];
                                    }

                                }
                            }
                            $html .= $value . '</td>';
                        }
                    }
                    $html .= '</tr>';
                }
            }
            $html .= '<tbody>';

            $html .= '<tfoot>';
            $html .= ' <tr>';
            $html .= '    <td colspan="10" class="text-center">';
            if(!is_array($items) && in_array('render',get_class_methods($items))){
                $html .=$items->appends(Input::all())->render(new PagerPresenter($items));
            }else if(!empty($page)){
                $html .= Form::make_pager($page);
            }
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= '</tfoot>';
            $html .= '</table></div></div>';
            $js = "<script>init.push(function(){
                   $('.fancybox').fancybox({
                    openEffect  : 'none',
                    closeEffect : 'none'
  });
                });</script>";
            $html .= $js;
            return $html;
        });
    }



    private function groupLabel()
    {
        Form::macro('group_label', function ($name, $label) {
            $value = ForoneHtmlServiceProvider::parseValue($this->model, $name);
            return '<div class="control-group">
                        <label for="title" class="control-label">' . $label . '</label>
                        <div class="controls">
                            <label for="title" class="control-label">' . $value . '</label>
                        </div>
                    </div>';
        });
    }


    public function panelStart()
    {
        Form::macro('panel_start', function ($title = '') {
            return '<div class="panel panel-default">
                        <div class="panel-heading bg-white">
                            <span class="font-bold">' . $title . '</span>
                        </div>
                    <div class="panel-body">';
        });
    }

    public function panelEnd()
    {
        Form::macro('panel_end', function ($submit_label = '') {
            if (!$submit_label) {
                return '';
            }
            $result = '</div><footer class="panel-footer">';
            if(is_array($submit_label)){
                $keys = array_keys($submit_label);

                foreach($keys as $key){
                    switch($key){
                        case 'submit':
                            $text = '提交';
                            if(!is_bool($submit_label['submit'])){
                                $text = $submit_label['submit'];
                            }
                            $result .= '<button type="submit" class="btn btn-info" style="margin-left:2%">' . $text . '</button>';
                            break;
                        case 'callback':
                            $url = !is_bool($submit_label['callback']) ? $submit_label['callback'] : \URL::previous();
                            $result .= '<a href="'.$url.'" class="btn btn-warning" style="margin-left:2%">返回</a>';
                            break;
                        case 'button':
                            $button = $submit_label['button'];
                            $text = $button['text'];
                            if(isset($button['url'])){
                                $url = $button['url'];
                                $result .= '<a href="'.$url.'" class="btn btn-primary" style="margin-left:2%">'.$text.'</a>';
                            }elseif(isset($button['click'])){
                                $clickAction = $button['click'];
                                $result .= '<a onclick="'.$clickAction.'" class="btn btn-primary" style="margin-left:2%">'.$text.'</a>';

                            }

                            break;
                    }
                }
            }else{
                $result .= '<button type="submit" class="btn btn-info" style="margin-left:2%">' . $submit_label . '</button>';
            }
            $result .='</footer></div>';
            return $result;
        });
    }


    public function modalButton()
    {
        Form::macro('modal_button', function ($label, $modal, $data, $class = 'waves-effect') {
            $jsonData = json_encode($data);
            $html = '<a href="' . $modal . '" style="margin-left:5px;"><button onclick="fillModal(\'' . $data['id'] . '\')" class="btn ' . $class . '" >' . $label . '</button></a>';
            $js = "<script>init.push(function(){datas['" . $data['id'] . "']='" . $jsonData . "';})</script>";
            return $html . $js;
        });
    }

    private function modalStart()
    {
        Html::macro('modal_start', function ($id, $title) {
            $html = '<div id="' . $id . '" class="remodal" data-remodal-id="' . $id . '">
                    <input type="hidden">
                    <div>
                        <span style="font-size: 20px">' . $title . '</span>
                    </div>
                    <div class="panel-body" style="margin: 35px 0px;padding: 0;">';
            return $html;
        });
    }

    private function json()
    {
        Html::macro('json', function ($data) {
            return '<pre><code>' . json_encode($data, JSON_PRETTY_PRINT) . '</code></pre>';
        });
    }

    private function modalEnd()
    {
        Html::macro('modal_end', function () {
            return '</div><div><button data-remodal-action="cancel" class="remodal-cancel" style="margin-right: 20px;">取消</button>
        <button data-remodal-action="confirm" class="remodal-confirm">确认</button></div>';
        });
    }

    private function datagridHeader()
    {
        $handler = function ($data) {
            $html = '<div class="panel panel-default">';
            $title = isset($data['title']) ? $data['title'] : '';
            $html .= '<div class="panel-heading">' . $title . '</div>';
            $html .= '<div class="panel-body b-b b-light"><form action="'.\Request::getPathInfo().'" method="GET">';

            $dataKeys = array_keys($data);
            foreach($dataKeys as $key){
                switch($key){
                    case 'new' :
                        $html .= '<div class="col-md-1" style="width: 5% ;margin:0 1px;">
                        <a href="' . $this->url->current() . '/create" class="btn btn-primary">&#43; 新增</a>
                        </div>';
                        break;
                    case 'priceStart' :
                        $priceStart = is_bool($data['priceStart']) ? '价格' : $data['priceStart'];
                        $html .= '<div class="col-md-3" style="padding-left:0px;width: 8%">
                                <input id="priceStartInput" type="text" class="form-control input" name="priceStart" value="'.Input::get('priceStart').'" placeholder="'.$priceStart.'"  />
                            </div>';
                        $js = "<script>init.push(function(){
                            $('#priceStartInput').keyup(function(event){
                                if(event.keyCode == 13){
                                    console.log('do search');
                                    var params = window.location.search.substring(1);
                                    var paramObject = {};
                                    var paramArray = params.split('&');
                                    paramArray.forEach(function(param){
                                        if(param){
                                            var arr = param.split('=');
                                            paramObject[arr[0]] = arr[1];
                                        }
                                    });
                                    var baseUrl = window.location.origin+window.location.pathname;
                                    if($(this).val()){
                                         if($('#priceEndInput').val())
                                            {
                                                if(parseFloat($('#priceEndInput').val()) >= parseFloat($(this).val())){
                                                     paramObject[$('#priceEndInput').attr('name')] = $('#priceEndInput').val();
                                                }else{
                                                    alert('范围有错');
                                                    return;
                                                }
                                            }
                                         paramObject[$(this).attr('name')] = $(this).val();
                                    }else{
                                        delete paramObject[$(this).attr('name')];
                                    }
                                    window.location.href = $.param(paramObject) ? baseUrl+'?'+$.param(paramObject) : baseUrl;
                                }
                            });
                        });</script>";
                        $html .= $js;
                        break;
                    case 'priceEnd' :
                        $priceEnd = is_bool($data['priceEnd']) ? '价格' : $data['priceEnd'];
                        $html .= '<div class="col-md-3" style="padding-left:0px;width: 8%">
                                <input id="priceEndInput" type="text" class="form-control input" name="priceEnd" value="'.Input::get('priceEnd').'" placeholder="'.$priceEnd.'"  />
                            </div>';
                        $js = "<script>init.push(function(){
                            $('#priceEndInput').keyup(function(event){
                                if(event.keyCode == 13){
                                    console.log('do search');
                                    var params = window.location.search.substring(1);
                                    var paramObject = {};
                                    var paramArray = params.split('&');
                                    paramArray.forEach(function(param){
                                        if(param){
                                            var arr = param.split('=');
                                            paramObject[arr[0]] = arr[1];
                                        }
                                    });
                                    var baseUrl = window.location.origin+window.location.pathname;
                                    if($(this).val()){
                                        if($('#priceStartInput').val())
                                        {
                                            if(parseFloat($('#priceStartInput').val()) <= parseFloat($(this).val())){
                                                 paramObject[$('#priceStartInput').attr('name')] = $('#priceStartInput').val();
                                            }else{
                                                alert('范围有错');
                                                return;
                                            }
                                        }
                                        paramObject[$(this).attr('name')] = $(this).val();
                                    }else{
                                        delete paramObject[$(this).attr('name')];
                                    }
                                    window.location.href = $.param(paramObject) ? baseUrl+'?'+$.param(paramObject) : baseUrl;
                                }
                            });
                        });</script>";
                        $html .= $js;
                        break;
                    case 'timeStart' :
                        $timeStart = $data['timeStart'];
                        $placeholder = is_bool($timeStart['placeholder']) ? '开始时间' : $timeStart['placeholder'];
                        $timeFormat = isset($timeStart['timeFormat']) ? $timeStart['timeFormat']:'Y-m-d H:i:s';
                        $html .= '<div class="col-md-2 col-sm-2" style="padding-left:0px;">
                                <input id="timeStartInput" type="text" class="form-control input" name="timeStart" value="'.Input::get('timeStart').'" placeholder="'.$placeholder.'"  />
                            </div>';
                        $js = "<script>init.push(function(){
                    jQuery('#timeStartInput').datetimepicker({format:'".$timeFormat."'});
                        $('#timeStartInput').keyup(function(event){
                            if(event.keyCode == 13){
                                console.log('do search');
                                var params = window.location.search.substring(1);
                                var paramObject = {};
                                var paramArray = params.split('&');
                                paramArray.forEach(function(param){
                                    if(param){
                                        var arr = param.split('=');
                                        paramObject[arr[0]] = arr[1];
                                    }
                                });
                                var baseUrl = window.location.origin+window.location.pathname;
                                if($(this).val()){
                                     if($('#timeEndInput').val())
                                        {
                                            if($('#timeEndInput').val() >= $(this).val()){
                                                 paramObject[$('#timeEndInput').attr('name')] = $('#timeEndInput').val();
                                            }else{
                                                alert('范围有错');
                                                return;
                                            }
                                        }
                                     paramObject[$(this).attr('name')] = $(this).val();
                                }else{
                                    delete paramObject[$(this).attr('name')];
                                }
                                window.location.href = $.param(paramObject) ? baseUrl+'?'+$.param(paramObject) : baseUrl;
                            }
                        });
                    });</script>";
                        $html .= $js;
                        break;
                    case 'timeEnd' :
                        $timeEnd = $data['timeEnd'];
                        $placeholder = is_bool($timeEnd['placeholder']) ? '结束时间' : $timeEnd['placeholder'];
                        $timeFormat = isset($timeEnd['timeFormat']) ? $timeEnd['timeFormat']:'Y-m-d H:i:s';
                        $html .= '<div class="col-md-2 col-sm-2" style="padding-left:0px;">
                                <input id="timeEndInput" type="text" class="form-control input" name="timeEnd" value="'.Input::get('timeEnd').'" placeholder="'.$placeholder.'"  />
                            </div>';
                        $js = "<script>init.push(function(){
                        jQuery('#timeEndInput').datetimepicker({format:'".$timeFormat."'});
                        $('#timeEndInput').keyup(function(event){
                            if(event.keyCode == 13){
                                console.log('do search');
                                var params = window.location.search.substring(1);
                                var paramObject = {};
                                var paramArray = params.split('&');
                                paramArray.forEach(function(param){
                                    if(param){
                                        var arr = param.split('=');
                                        paramObject[arr[0]] = arr[1];
                                    }
                                });
                                var baseUrl = window.location.origin+window.location.pathname;
                                if($(this).val()){
                                    if($('#timeStartInput').val())
                                    {
                                        if($('#timeStartInput').val() <= $(this).val()){
                                             paramObject[$('#timeStartInput').attr('name')] = $('#timeStartInput').val();
                                        }else{
                                            alert('范围有错');
                                            return;
                                        }
                                    }
                                    paramObject[$(this).attr('name')] = $(this).val();
                                }else{
                                    delete paramObject[$(this).attr('name')];
                                }
                                window.location.href = $.param(paramObject) ? baseUrl+'?'+$.param(paramObject) : baseUrl;
                            }
                        });
                    });</script>";
                        $html .= $js;
                        break;
                    case 'filters' :
                        foreach ($data['filters'] as $key => $value) {
                            $html .= '<div class="col-md-6 col-sm-6 form-group" style="margin-bottom: 0px;padding-left: 0">
                                        <label class="col-md-3 col-sm-3 text-right" style="line-height: 35px;">'.$value['text'].'</label>
                                        <select class="form-control"  style="width: 75%;margin-right: 0;display:inline-block;" name="' . $key . '">';
                            foreach ($value['select'] as $item) {
                                $value = is_array($item) ? $item['value'] : $item;
                                $label = is_array($item) ? $item['label'] : $item;
                                $selected = '';
                                $urlValue = Input::get($key);
                                if ($urlValue != null) {
                                    $selected = $urlValue == $item['value'] ? 'selected="selected"' : '';
                                }
                                $html .= '<option ' . $selected . ' value="' . $value . '">' . $label . '</option>';
                            }

                            $html .= '</select></div>';
                        }
                        /*$js = "<script>init.push(function(){
                            $('select').change(function(){
                                var params = window.location.search.substring(1);
                                var paramObject = {};
                                var paramArray = params.split('&');
                                paramArray.forEach(function(param){
                                    if(param){
                                        var arr = param.split('=');
                                        paramObject[arr[0]] = arr[1];
                                    }
                                });
                                var baseUrl = window.location.origin+window.location.pathname;
                                if($(this).val()){
                                    paramObject[$(this).attr('name')] = $(this).val();
                                }else{
                                    delete paramObject[$(this).attr('name')];
                                }
                                window.location.href = $.param(paramObject) ? baseUrl+'?'+$.param(paramObject) : baseUrl;
                            });
                        })</script>";
                        $html .= $js;*/
                        break;
                    case 'timePickerGroup':
                        $timePickerGroup = $data['timePickerGroup'];

                        foreach($timePickerGroup as $index=>$item){
                            $timeFormat = isset($item['timeFormat']) ? $item['timeFormat']:'Y-m-d H:i:s';
                            $placeholder = $item['placeholder'];
                            $html .= '
                                <div class="col-md-6 col-sm-6" style="padding-left:0px;height: 39px">
                                    <label class="col-md-3 col-sm-3 text-right" style="line-height: 34px;margin-bottom: 0;">'.$item['text'].'</label>
                                    <input id="'.$index.'Start" name="'.$index.'Start" type="text"  class="form-control"  style="width: 36.5%;margin-right: 0;display:inline-block;"   value="'.Input::get($index.'Start').'" placeholder="'.$placeholder.'开始"  />
                                    <span style="display:inline-block;width: 0.3%;">-</span>
                                    <input id="'.$index.'End" name="'.$index.'End" type="text"  class="form-control"  style="width: 36.5%;margin-right: 0;display:inline-block;"  value="'.Input::get($index.'End').'" placeholder="'.$placeholder.'结束"  />
                                </div>';
                            $js = "<script>
                                        init.push(function(){
                                            jQuery('#".$index."Start').datetimepicker({format:'".$timeFormat."'});
                                            jQuery('#".$index."End').datetimepicker({format:'".$timeFormat."'});
                                        });
                                    </script>";
                            $html .= $js;
                        }
                        break;
                    case 'numberGroup':
                        $numberGroup = $data['numberGroup'];

                        foreach($numberGroup as $index=>$item){
                            $placeholder = isset($item['placeholder']) ? $item['placeholder'] : '数值';
                            $html .= '
                                <div class="col-md-6 col-sm-6" style="padding-left:0px;height: 39px">
                                    <label class="col-md-3 col-sm-3 text-right" style="line-height: 34px;margin-bottom: 0;">'.$item['text'].'</label>
                                    <input id="'.$index.'Min" name="'.$index.'Min" type="number" step="0.0000001" class="form-control" style="width: 36.5%;margin-right: 0;display:inline-block;"   value="'.Input::get($index.'Min').'" placeholder="'.$placeholder.'开始"  />
                                    <span style="display:inline-block;width: 0.3%;">-</span>
                                    <input id="'.$index.'Max" name="'.$index.'Max" type="number" step="0.0000001" class="form-control" style="width: 36.5%;margin-right: 0;display:inline-block;"  value="'.Input::get($index.'Max').'" placeholder="'.$placeholder.'结束"  />
                                </div>';
                        }
                        break;

                    case 'search' :
                        $search = is_bool($data['search']) ? '请输入您想检索的信息' : $data['search'];
                        if(is_array($search)){
                            foreach($search as $item){
                                $keyword = $item['key'];
                                $text = $item['text'];
                                $placeholder = isset($item['placeholder']) ? $item['placeholder'] : '';
                                $html .= '<div class="col-md-6 col-sm-6" style="padding-left:0px;">
                                <label class="col-md-3 col-sm-3 text-right control-label" style="line-height: 34px;">'.$text.'</label>
                                <input id="keywordsInput" type="text" class="form-control input" style="width: 75%;margin-right: 0;display:inline-block;" name="'.$keyword.'" value="' . Input::get($keyword) . '" placeholder="' . $placeholder . '"  />
                            </div>';
                            }
                        }else{
                            $html .= '<div class="col-md-2" style="padding-left:0px; width: 17%">
                                <input id="keywordsInput" type="text" class="form-control input" name="keywords" value="' . Input::get('keywords') . '" placeholder="' . $search . '"  />
                            </div>';
                            $js = "<script>init.push(function(){
                            $('#keywordsInput').keyup(function(event){
                                if(event.keyCode == 13){
                                    //console.log('do search');
                                    var params = window.location.search.substring(1);
                                    var paramObject = {};
                                    var paramArray = params.split('&');
                                    paramArray.forEach(function(param){
                                        if(param){
                                            var arr = param.split('=');
                                            paramObject[arr[0]] = arr[1];
                                        }
                                    });
                                    var baseUrl = window.location.origin+window.location.pathname;
                                    if($(this).val()){
                                        paramObject[$(this).attr('name')] = $(this).val();
                                    }else{
                                        delete paramObject[$(this).attr('name')];
                                    }
                                    window.location.href = $.param(paramObject) ? baseUrl+'?'+$.param(paramObject) : baseUrl;
                                }
                            });
                            });</script>";
                            $html .= $js;
                        }


                        break;
                    case 'submit' :
                        $search = is_bool($data['submit']) ? '搜索' : $data['submit'];
                        $html .= '<div class="col-md-1" style="width: 5% ;margin:0 1px;">
                                <button type="submit" class="btn btn-primary"> '.$search.'</button>
                            </div>';
                        break;
                    case 'callback' :
                        $search = is_bool($data['callback']) ? '返回' : $data['callback'];
                        if(is_array($search)){
                            $text = $search['text'];
                        }else{
                            $text = $search;
                        }
                        $url = isset($search['url']) ? $search['url'] : \URL::previous();
                        $html .= '<div class="col-md-1" style="width: 6%;margin:0 1px;">
                                <a class="btn btn-primary" href="'.$url.'"> '.$text.'</a>
                            </div>';
                        break;
                    case 'button':
                        $button = $data['button'];
                        $url = $button['url'];
                        $text = $button['text'];
                        $html .= '<div class="col-md-1" style="width: 6%">
                                    <a class="btn btn-default" href="'.$url.'">'.$text.'</a>
                                </div>';
                        break;
                    case 'label':
                        $text = $data['label'];
                        $html .= '<div class="col-md-6 pull-right">
                                    <label class="label label-info" >'.$text.'</label>
                                </div>';
                        break;
                }
            }
            $html .= '</form></div>';
            return $html;
        };
        Html::macro('list_header', $handler);
        Html::macro('datagrid_header', $handler);
    }

    private function datagridFooter()
    {
        Html::macro('footer', function ($data) {
            return $data ? $data->render() : '';
        });
    }
}