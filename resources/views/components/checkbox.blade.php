<input type="checkbox" {!! $attributes->merge(['class'=>'form-check-input'])->except('checked') !!} @checked($attributes->get('checked'))>
