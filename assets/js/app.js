
function qs(s,el){return (el||document).querySelector(s)}

function togglePassword(id,iconId){
  const inp = qs('#'+id); const ic = qs('#'+iconId);
  if(!inp) return;
  if(inp.type==='password'){ inp.type='text'; ic.setAttribute('data-eye','open'); }
  else { inp.type='password'; ic.setAttribute('data-eye','closed'); }
}
function submitSwitchOrg(sel){
  if(!sel || !sel.value) return;
  const form = document.createElement('form');
  form.method='POST'; form.action=(window.BASE_URL||'') + '/auth/switch_org.php';
  const input = document.createElement('input'); input.type='hidden'; input.name='organization_id'; input.value=sel.value;
  form.appendChild(input); document.body.appendChild(form); form.submit();
}
