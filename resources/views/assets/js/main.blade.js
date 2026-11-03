// 任何需要 Laravel 寫入東西 (ex. username , formID) 的 Javascript 才放在這 其餘放 public/js

window.baseURL = "{{url('')}}/";
window.pageID = "{{ $PAGE_ID ?? session('PAGE_ID') }}";
window.formType = "{{ $type ?? ''}}";
window.csrfToken = "{{csrf_token()}}";
// 草稿 / 保活 (public/js/draft.js)
window.draftUser = "{{ auth()->id() ?? 0 }}";
window.draftScope = "{{ substr(md5(config('database.connections.' . config('database.default') . '.database')), 0, 6) }}_{{ auth()->id() ?? 0 }}";
// 啟用草稿功能的頁面代碼; 要全部表單都啟用就改成 ["*"]
window.draftEnabledPages = ["*"];

window.urls = {};
window.urls.keepalive = "{{route('system.keepalive')}}"

window.urls.getReferenceData = "{{route('system.reference.getReferenceData',['field_id' => '-field_id-'])}}"
window.urls.download = "{{route('system.download',['fieldID' => '-fieldID-', 'filename' => '-filename-', 'id' => '-id-'])}}"

window.commonTranslations = @json($commonTranslations);
