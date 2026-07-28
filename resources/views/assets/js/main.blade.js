// 任何需要 Laravel 寫入東西 (ex. username , formID) 的 Javascript 才放在這 其餘放 public/js

window.baseURL = "{{url('')}}/";
window.pageID = "{{ $PAGE_ID ?? session('PAGE_ID') }}";
window.formType = "{{ $type ?? ''}}";
window.csrfToken = "{{csrf_token()}}";

window.urls = {};

window.urls.getReferenceData = "{{route('system.reference.getReferenceData',['field_id' => '-field_id-'])}}"
window.urls.download = "{{route('system.download',['fieldID' => '-fieldID-', 'filename' => '-filename-', 'id' => '-id-'])}}"

window.commonTranslations = @json($commonTranslations);
