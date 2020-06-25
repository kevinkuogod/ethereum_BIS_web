<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>測試追朔</title>
    </head>
    <body>
        <div style="color: #A830FF;border:2px #ccc solid;padding:1% 40% 1% 40%;margin:0% 0% 1% 0%;">追朔系統(RFID輸入頁面)</div>
        <div>
            <input type="text" placeholder="請輸入資產編號" id='asset_ID'></input>
            <button onclick="get_main_leager_site()">搜尋</button>
        </div>
        <div id="hide_from"></div>
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
        function get_main_leager_site(){
            if(ajax_lock == 0){
                ajax_lock = 1;
                let form_obj = document.createElement("form");
                form_obj.setAttribute("action","{{ route('trace.list_asset_main_leager_site') }}");
                form_obj.setAttribute("method","post");
                let input_obj = document.createElement("input");
                input_obj.type = "text";
                input_obj.name = "asset_ID_value";
                input_obj.value = document.getElementById('asset_ID').value;
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

                // //part 1
                // let form = new FormData();
                // let request = new ajaxRequest();
                // let asset_ID_obj = document.getElementById('asset_ID');
                // form.append("asset_ID_value", asset_ID_obj.value);
                // load_start();
                // request.open("POST", "{{ route('trace.get_switch_site_data') }}");
                // request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");//取第一個csrf來做判斷
                // request.send(form);
                // request.onreadystatechange = function() {
                //     if (request.readyState === 4) {
                //         if (request.status === 200) {
                //             console.log(request.responseText);
                //             console.log('上傳成功');
                //             load_end();
                //             ajax_lock = 0;
                //         }
                //     }else{
                //         console.log(request.responseText);
                //     }
                // }
            }          
        }

        ajax_lock = 0
        //loading
        function load_start(){
            load.init();
            load.start();
        }

        function load_end(){
            load.stop();
        }

    </script>
</html>
