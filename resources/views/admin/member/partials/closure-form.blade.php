<div class="row g-3">
  <div class="col-12"><label class="form-label" for="{{ $prefix }}_type">Тип исключения</label><select class="form-select" id="{{ $prefix }}_type" name="type" required>@foreach(\App\Models\RedDay::TYPES as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
  <div class="col-12"><label class="form-label" for="{{ $prefix }}_name">Название</label><input class="form-control" id="{{ $prefix }}_name" name="name" required maxlength="190" placeholder="Например: отпуск или врач"></div>
  <div class="col-12"><label class="form-label" for="{{ $prefix }}_description">Описание</label><textarea class="form-control" id="{{ $prefix }}_description" name="description" maxlength="500" rows="2"></textarea></div>
  <div class="col-6"><label class="form-label" for="{{ $prefix }}_date">Дата от</label><input class="form-control" id="{{ $prefix }}_date" name="date" type="date" required></div>
  <div class="col-6"><label class="form-label" for="{{ $prefix }}_date_to">Дата до</label><input class="form-control" id="{{ $prefix }}_date_to" name="date_to" type="date"></div>
  <div class="col-12"><div class="form-check"><input type="hidden" name="full_day" value="0"><input class="form-check-input" id="{{ $prefix }}_full_day" name="full_day" type="checkbox" value="1" checked><label class="form-check-label" for="{{ $prefix }}_full_day">Весь день</label></div></div>
  <div class="col-6"><label class="form-label" for="{{ $prefix }}_start_time">Начало</label><input class="form-control {{ $prefix }}-time" id="{{ $prefix }}_start_time" name="start_time" type="time"></div>
  <div class="col-6"><label class="form-label" for="{{ $prefix }}_end_time">Конец</label><input class="form-control {{ $prefix }}-time" id="{{ $prefix }}_end_time" name="end_time" type="time"></div>
  <div class="col-12"><div class="form-check"><input type="hidden" name="repeat" value="0"><input class="form-check-input" id="{{ $prefix }}_repeat" name="repeat" type="checkbox" value="1"><label class="form-check-label" for="{{ $prefix }}_repeat">Повторять ежегодно</label></div></div>
</div>
