<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('css/loading.css') }}">

        <title>測試追朔</title>
    </head>
    <body>
        <div style="color: #A830FF;border:2px #ccc solid;padding:1% 30% 1% 40%;margin:0% 0% 1% 0%;">追溯系統(供應鏈階段查詢頁面)</div>
        <div id="system_error_message"  style="color:#880000;border:2px #ccc solid;padding:10px;;margin:0% 0% 1% 0%;"></div>
        <div>
            <table class="table" id="leager_table" border="1">
                <thead>
                    <tr>
                        <td class="col-md-1">場景名稱</td>
                        <td class="col-md-2">查詢</td>
                        <td class="col-md-2">狀態</td>
                        <td class="col-md-2">驗證時間</td>
                        <td class="col-md-2">錯誤資產RFID編號</td>
                        <td class="col-md-2">重新驗證</td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="hide_from"></div>
        </div>
    </body>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/loading_fron.js') }}"></script>

    <script>
        function ajaxRequest() {
            try {
                // （除 IE7 之前）支援所有現代瀏覽器
                request = new XMLHttpRequest();
            }catch(e1){
                try {
                    // （支援 IE6）如果有的話就用 ActiveX 物件的最新版本
                    request = new ActiveXObject("Msxml2.XMLHTTP.6.0");
                }catch (e2){
                    try {
                        // （支援 IE5）否則就用較舊的版本
                        request = new ActiveXObject("Msxml2.XMLHTTP.3.0");
                    }catch (e3) {
                        // 不支援 Ajax，拋出錯誤
                        throw new Error("XMLHttpRequest is not supported");
                    }
                }
            }
            return request;
        }
        let load = new Loading();
        ajax_lock = 0
        //loading
        function load_start(){
            load.init();
            load.start();
        }

        function load_end(){
            load.stop();
        }

        function get_switch_site_data(leager_site_id){
            let system_error_message_obj = document.getElementById('system_error_message');

            if((leager_site_id != 'null') && (system_error_message_obj.innerText == "")){
                let form_obj = document.createElement("form");
                form_obj.setAttribute("action","{{ route('trace.get_switch_site_data') }}");
                form_obj.setAttribute("method","post");
                let input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "leager_site_id";
                input_obj.value = leager_site_id;
                form_obj.appendChild(input_obj);

                input_obj = document.createElement("input");
                input_obj.type = "hidden";
                input_obj.name = "_token";
                input_obj.value = '{{ csrf_token() }}';
                form_obj.appendChild(input_obj);


                input_obj = document.createElement("input");
                input_obj.type = "submit";

                form_obj.setAttribute("id","submit_input");
                form_obj.appendChild(input_obj);

                let hide_from_obj = document.getElementById('hide_from');
                hide_from_obj.appendChild(form_obj);

                let submit_input_obj = document.getElementById('submit_input');
                submit_input_obj.submit();
            }else if(leager_site_id == 'null'){
                alert("供應鏈 trace_asset_in_leagersite 資料表被篡改");
            }else if(system_error_message_obj.innerText != ""){
                alert(system_error_message_obj.innerText);
            }
        }

        function re_validation_background(asset_RFID_id, leager_site_table_data_id, leager_site_pos){
            let system_error_message_obj = document.getElementById('system_error_message');

            if((leager_site_table_data_id != 'null') && (system_error_message_obj.innerText == "")){
                let form = new FormData();
                let request = new ajaxRequest();
                form.append("asset_RFID_ID", asset_RFID_id);
                form.append("leager_site_table_data_id", leager_site_table_data_id);
                form.append("leager_site_pos", leager_site_pos);
                load_start();
                request.open("POST", "{{ route('trace.manual_trigger_background_job') }}");
                request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");//取第一個csrf來做判斷
                request.send(form);
                request.onreadystatechange = function() {
                    if (request.readyState === 4) {
                        if (request.status === 200) {
                            console.log(request.responseText);
                            console.log('等候時間');
                            load_end();
                            ajax_lock = 0;
                            // location.reload();
                        }
                    }else{
                        console.log(request.responseText);
                    }
                }
            }else if(leager_site_table_data_id == 'null'){
                alert("供應鏈 trace_asset_in_leagersite 資料表被篡改");
            }else if(system_error_message_obj.innerText != ""){
                alert(system_error_message_obj.innerText);
            }
        }
        
        //初始化
        @if(isset($leager_site_table_data))
            let leager_table_obj = document.getElementById('leager_table');
            let Tr,a_obj,button_obj,leager_site_tmp_id,select_obj;
            @for ($i = 0; $i < count($leager_site_table_data); $i++)
                Tr = leager_table_obj.insertRow(leager_table_obj.rows.length);
                Td = Tr.insertCell(Tr.cells.length);
                Td.innerText = "{{ $leager_site_table_data[$i]['leager_site_name'] }}";

                Td = Tr.insertCell(Tr.cells.length);
                button_obj = document.createElement("button");
                button_obj.setAttribute("type","button");
                button_obj.setAttribute("onclick",'get_switch_site_data("'+"{{ $leager_site_table_data[$i]['id'] }}"+'")');
                button_obj.innerText="查詢供應鏈階段資料";
                Td.appendChild(button_obj);

                Td = Tr.insertCell(Tr.cells.length);
                Td.innerText = "{{ $leager_site_table_data[$i]['leager_site_validation_status'] }}";

                Td = Tr.insertCell(Tr.cells.length);
                Td.innerText = "{{ $leager_site_table_data[$i]['leager_site_error_time'] }}";

                Td = Tr.insertCell(Tr.cells.length);
                select_obj = document.createElement("select");
                select_obj.id='asset_'+{{ $i }};
                @for ($i_asset_obj = 0; $i_asset_obj < count($leager_site_table_data[$i]['leager_site_error_item']); $i_asset_obj++)
                    select_obj.options[select_obj.length] = new Option("{{ $leager_site_table_data[$i]['leager_site_error_item'][$i_asset_obj] }}", "{{ $leager_site_table_data[$i]['leager_site_error_item'][$i_asset_obj] }}");
                @endfor
                Td.appendChild(select_obj);

                Td = Tr.insertCell(Tr.cells.length);
                button_obj = document.createElement("button");
                button_obj.setAttribute("type","button");
                button_obj.setAttribute("onclick",'re_validation_background("'+"{{ $asset_RFID_ID }}"+'","'+"{{ $leager_site_table_data[$i]['id'] }}"+'","'+"{{ $i }}"+'")');
                button_obj.innerText="重新驗證";
                Td.appendChild(button_obj);
            @endfor
        @endif

        @if(isset($error_str))
            let system_error_message_obj = document.getElementById('system_error_message');
            system_error_message_obj.innerText= "{{ $error_str }}".replace(/text_return/g,"\n");
        @endif
    </script>
</html>
