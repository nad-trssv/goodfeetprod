<script>
document.addEventListener('change', async event => {
  const select = event.target.closest('.appointment-status-select');
  if (!select || !select.value) return;
  const status = select.value;
  const current = select.dataset.current;
  const terminal = ['completed','no_show','cancelled_by_client','cancelled_by_business','rescheduled'];
  const isCorrection = terminal.includes(current) && status !== current;
  let reason = null;
  if (isCorrection) {
    reason = window.prompt(@json(__('appointment_statuses.correction_reason')));
    if (reason === null || reason.trim().length < 3) {
      select.value = '';
      if (reason !== null) alert(@json(__('appointment_statuses.reason_required')));
      return;
    }
  }
  select.disabled = true;
  const response = await fetch(`/appointments/${select.dataset.id}/status`, {
    method: 'PATCH',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json'},
    body: JSON.stringify({status, reason})
  });
  if (response.ok) { location.reload(); return; }
  select.disabled = false;
  select.value = '';
  const data = await response.json();
  alert(data.message || Object.values(data.errors || {}).flat()[0] || @json(__('appointment_statuses.update_failed')));
});
</script>
