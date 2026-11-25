(function () {
  const cfg = window.quoteBuilderConfig;
  if (!cfg) return;
  const products = cfg.products || [];
  const settings = cfg.settings || {};
  const K = n => 'K' + Math.round(Number(n || 0)).toLocaleString();
  const pc = n => (Number(n || 0)).toFixed(1) + '%';
  const byId = id => document.getElementById(id);
  const state = { lines: (cfg.invoice?.items || []).map(normalizeLine) };
  const brandMap = { none:[0,0], screen:[+settings.quote_brand_screen_cost,+settings.quote_brand_screen_setup], embroid:[+settings.quote_brand_embroid_cost,+settings.quote_brand_embroid_setup], full:[+settings.quote_brand_full_cost,+settings.quote_brand_full_setup], leather_patch:[+settings.quote_brand_leather_patch_cost,+settings.quote_brand_leather_patch_setup] };

  function normalizeLine(l){ return { product_id:+l.product_id, quantity:Math.max(1,+l.quantity||1), channel:l.channel||'retail', branding:l.branding||'none', payment_days:Math.max(0,+l.payment_days||0), rush:!!l.rush, manual_discount_percent:+l.manual_discount_percent||0, strategic_discount_percent:+l.strategic_discount_percent||0 }; }
  function volDisc(qty, payDays){ if (payDays > 15) return 0; if (qty>=500) return +settings.quote_vol_tier_4/100; if (qty>=200) return +settings.quote_vol_tier_3/100; if (qty>=50) return +settings.quote_vol_tier_2/100; return +settings.quote_vol_tier_1/100; }
  function brandingCalc(brand, qty){ const [contractor, setup]=brandMap[brand]||[0,0]; const margin=Math.min(.95,Math.max(0,+settings.quote_brand_margin/100)); const setupPerUnit=brand==='none'?0:setup/Math.max(1,qty); const brandCost=brand==='none'?0:(contractor+setupPerUnit)/(1-margin); return {contractor,setup,setupPerUnit,brandCost}; }
  function numSetting(key){
    const v = Number(settings[key]);
    if (Number.isFinite(v)) return v;
    throw new Error(`Missing quote setting: ${key}`);
  }
  function cbRates(){
    const labour = numSetting('quote_cb_labour');
    const sal = numSetting('quote_cb_sal_oh');
    const fac = numSetting('quote_cb_fac_oh');
    return { labour, sal, fac, combined: labour + sal + fac };
  }
  function calcLine(line){
    const p = products.find(x => +x.id === +line.product_id); if(!p) return null;
    const qty=Math.max(1,+line.quantity||1), brand=line.branding||'none', payDays=Math.max(0,+line.payment_days||0), rush=!!line.rush;
    const discPct=Math.min(80, Math.max(0,+line.manual_discount_percent||0)+Math.max(0,+line.strategic_discount_percent||0));
    const cost=+p.cost_price, retail=+p.price; const b=brandingCalc(brand,qty);
    const instBase = retail*(1-volDisc(qty,payDays));
    let inst = (instBase + b.brandCost) * (1 + (payDays/365)*(+settings.quote_pay_interest_annual/100));
    if (rush) inst *= (1 + (+settings.quote_rush_premium/100));
    const instCost = cost + b.contractor + b.setupPerUnit;
    const map = {
      retail:{price:retail,cost,label:'MPS Retail'}, partner:{price:retail*(1-(+settings.quote_partner_margin/100)),cost,label:'Retail Partner'},
      institutional:{price:inst,cost:instCost,label:'Institutional'}, employee:{price:p.employee_eligible?retail*(1-(+settings.quote_employee_discount/100)):null,cost,label:'Employee'},
      wholesale:{price:retail*(1-(+settings.quote_wholesale_discount/100)),cost,label:'Wholesale'}
    };
    const ch = map[line.channel] || map.retail; if (ch.price===null) return {...line, error:'Not employee eligible'};
    const base = ch.price, unit = base*(1-discPct/100), subtotal = unit*qty, lineCost=ch.cost*qty, margin=subtotal-lineCost;
    const mins = Math.max(0, +p.production_minutes || 0);
    const otMult = line.overtime ? numSetting(line.ot_type==='sunday' ? 'quote_ot_sunday' : 'quote_ot_weekday') : 1;
    const otPremium = line.overtime ? (mins * cbRates().labour * (otMult - 1) * qty) : 0;
    return {...line, product:p, qty, base, unit, subtotal, margin, marginPct:subtotal>0?(margin/subtotal*100):0, lineCost, channelLabel:ch.label, brandingMeta:b, otPremium, minutesTotal: mins*qty};
  }
  function totals(){
    const lines = state.lines.map(calcLine).filter(Boolean);
    const subtotal = lines.reduce((s,l)=>s+(l.subtotal||0),0);
    const docDiscPct = Math.max(0, Math.min(100, +byId('discount_percent').value||0));
    const docDisc = subtotal * docDiscPct/100;
    const taxable = subtotal - docDisc;
    const vatPct = Math.max(0, Math.min(100, +byId('tax_percent').value||numSetting('quote_vat_percent')));
    const vat = taxable * vatPct/100;
    const total = taxable + vat;
    const cost = lines.reduce((s,l)=>s+(l.lineCost||0),0);
    const margin = taxable - cost;
    return {lines, subtotal, docDiscPct, docDisc, vatPct, vat, total, cost, margin, marginPct: taxable>0?(margin/taxable*100):0};
  }
  function renderProducts(){
    byId('product_id').innerHTML = '<option value="">Select product...</option>' + products.map(p => `<option value="${p.id}">${p.name} — K${Math.round(+p.price).toLocaleString()}</option>`).join('');
  }
  function renderOverview(){
    byId('overviewBody').innerHTML = products.map(p => {
      const r=+p.price,c=+p.cost_price,partner=r*(1-(+settings.quote_partner_margin/100)),emp=p.employee_eligible?r*(1-(+settings.quote_employee_discount/100)):null,whole=r*(1-(+settings.quote_wholesale_discount/100));
      const gm=v=>v>0?((v-c)/v*100):0;
      return `<tr><td><strong>${p.name}</strong><div style="font-size:11px;color:var(--qb-g3)">${p.sku||''}</div></td><td class="r">${K(c)}</td><td class="r">${K(r)} <span class="${gm(r)>=50?'gm-hi':gm(r)>=30?'gm-mid':'gm-lo'}">(${pc(gm(r))})</span></td><td class="r">${K(partner)}</td><td class="r">${K(r)}</td><td class="r">${emp?K(emp):'<span style="color:var(--qb-g3)">N/A</span>'}</td><td class="r">${K(whole)}</td><td class="r">${p.model_list_price?K(p.model_list_price):'-'}</td><td class="r">${p.intl_usd_price?'$'+Math.round(+p.intl_usd_price).toLocaleString():'-'}</td><td><span class="chip ${p.quote_category==='Complex'?'chip-r':p.quote_category==='Medium'?'chip-a':'chip-g'}">${p.quote_category||'-'}</span></td></tr>`;
    }).join('') || '<tr><td colspan="10" style="padding:20px;text-align:center;color:var(--qb-g3)">No quote-enabled products found.</td></tr>';
  }
  function renderCostBreakdown(t){
    const card = byId('costBreakCard');
    if(!card) return;
    if(!t.lines.length){ card.style.display='none'; return; }
    card.style.display='block';
    const rates = cbRates();
    byId('cbRateLab').textContent = rates.labour.toFixed(4);
    byId('cbRateSal').textContent = rates.sal.toFixed(4);
    byId('cbRateFac').textContent = rates.fac.toFixed(4);
    byId('cbTotPct').textContent = totPercent;

    const monthlyCap = numSetting('quote_monthly_capacity_mins');
    const monthlyFixed = numSetting('quote_monthly_fixed');
    const totalMachinists = numSetting('quote_total_machinists');
    const workDays = numSetting('quote_work_days_month');
    const shiftHrs = numSetting('quote_shift_hours');
    const shiftEff = numSetting('quote_shift_efficiency');
    const totPercent = numSetting('quote_tot_percent');
    const quoteMins = t.lines.reduce((s,l)=>s + (l.minutesTotal||0), 0);
    const capPct = monthlyCap>0 ? Math.min(100, (quoteMins/monthlyCap*100)) : 0;
    const capPctFmt = capPct.toFixed(1);
    const fixedCovered = monthlyCap>0 ? (quoteMins/monthlyCap*monthlyFixed) : 0;
    const fixedBalance = monthlyFixed - fixedCovered;
    const capColor = capPct>=80 ? '#C0392B' : capPct>=50 ? '#E67E22' : '#1D9E75';
    const remMins = Math.max(0, monthlyCap - quoteMins);
    byId('capBanner').innerHTML = `
      <div style='background:var(--qb-g1);border-radius:8px;padding:10px 14px'>
        <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;gap:8px;flex-wrap:wrap'>
          <span style='font-size:11px;font-weight:600;color:#1a1a18'>Monthly Capacity Usage</span>
          <span style='font-size:11px;color:var(--qb-g3)'>Monthly cap: ${Math.round(monthlyCap).toLocaleString()} min (${totalMachinists} machinists · ${workDays} days · ${Math.round(shiftHrs*shiftEff)}h productive)</span>
        </div>
        <div style='height:10px;background:var(--qb-g2);border-radius:5px;overflow:hidden;margin-bottom:8px'>
          <div style='height:100%;width:${capPctFmt}%;background:${capColor};border-radius:5px;transition:width .4s'></div>
        </div>
        <div style='display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px'>
          <div style='text-align:center'><div style='font-size:15px;font-weight:700;color:${capColor}'>${capPctFmt}%</div><div style='font-size:10px;color:var(--qb-g3)'>${Math.round(quoteMins).toLocaleString()} min · this quote</div></div>
          <div style='text-align:center'><div style='font-size:15px;font-weight:700;color:#1a1a18'>${K(fixedCovered)}</div><div style='font-size:10px;color:var(--qb-g3)'>Fixed overhead covered</div></div>
          <div style='text-align:center'><div style='font-size:15px;font-weight:700;color:var(--qb-g3)'>${Math.round(remMins).toLocaleString()} min</div><div style='font-size:10px;color:var(--qb-g3)'>Remaining for retail &amp; other orders · ${K(fixedBalance)} balance</div></div>
        </div>
      </div>`;

    const vatRate = t.vatPct/100;
    let totRev=0, totMat=0, totLab=0, totSal=0, totFac=0, totBrand=0, totOTP=0;
    const rows = t.lines.map(l=>{
      const p=l.product||{};
      const mins = Math.max(0, +p.production_minutes||0) * l.qty;
      const mat = Math.max(0, (+p.cost_price - mins/l.qty*rates.combined)) * l.qty;
      const lab = mins * rates.labour;
      const sal = mins * rates.sal;
      const fac = mins * rates.fac;
      const brand = ((l.brandingMeta?.contractor||0) + (l.brandingMeta?.setupPerUnit||0)) * l.qty;
      const otp = l.otPremium || 0;
      const rev = l.subtotal;
      const tot = rev * (totPercent/100);
      const vat = rev * vatRate;
      const np = rev - tot - vat - mat - brand - sal - lab - otp - fac;
      totRev+=rev; totMat+=mat; totLab+=lab; totSal+=sal; totFac+=fac; totBrand+=brand; totOTP+=otp;
      return {name:p.name||'Item', qty:l.qty, rev, tot, vat, mat, brand, sal, lab, otp, fac, np};
    });
    const totTOT = totRev*(totPercent/100);
    const totVAT = totRev*vatRate;
    const totNP = totRev - totTOT - totVAT - totMat - totBrand - totSal - totLab - totOTP - totFac;
    const segs = [
      {label:`TOT (${totPercent}%)`, val:totTOT, color:'#C0392B'},
      {label:'VAT', val:totVAT, color:'#E67E22'},
      {label:'Materials', val:totMat, color:'#5B7FA6'},
      {label:'Branding', val:totBrand, color:'#9B59B6'},
      {label:'Salaries', val:totSal, color:'#C49B3C'},
      {label:'Dir. Labour', val:totLab, color:'#1D9E75'},
      {label:'OT Premium', val:totOTP, color:'#FF6B35'},
      {label:'Facility & Ops', val:totFac, color:'#4A9B7F'},
      {label:'Net Profit', val:Math.max(0,totNP), color:'#2E7D32'},
    ];
    const lossBar = totNP < 0 ? `<div class="cb-seg" style="background:var(--qb-red);width:${Math.min(100,Math.abs(totNP)/Math.max(1,totRev)*100).toFixed(1)}%">LOSS</div>` : '';
    byId('cbBar').innerHTML = segs.map(s=>{ const pct=totRev>0?(s.val/totRev*100):0; return `<div class="cb-seg" style="background:${s.color};width:${pct.toFixed(1)}%" title="${s.label}: ${pct.toFixed(1)}%">${pct>=5?pct.toFixed(1)+'%':''}</div>`; }).join('') + lossBar;
    byId('cbLegend').innerHTML = segs.map(s=>{ const pct=totRev>0?(s.val/totRev*100):0; return `<span><span class="cb-dot" style="background:${s.color}"></span>${s.label} · ${pct.toFixed(1)}% (${K(s.val)})</span>`; }).join('');
    const tiles = [
      {val:K(totRev),lbl:'Revenue (excl. VAT)',color:'var(--qb-navy)'},
      {val:K(totTOT),lbl:`TOT → ZRA (${totPercent}%)`,color:'#C0392B'},
      {val:K(totVAT),lbl:`VAT → ZRA (${Math.round(vatRate*100)}%)`,color:'#E67E22'},
      {val:K(totMat),lbl:'Materials',color:'#5B7FA6'},
      {val:K(totBrand),lbl:'Branding',color:'#9B59B6'},
      {val:K(totSal),lbl:'Salaries',color:'#C49B3C'},
      {val:K(totLab),lbl:'Direct Labour',color:'#1D9E75'},
      {val:K(totOTP),lbl:'OT Premium',color:'#FF6B35'},
      {val:K(totFac),lbl:'Facility & Ops',color:'#4A9B7F'},
      {val:K(totNP),lbl:'Net Profit',color:totNP>=0?'#2E7D32':'var(--qb-red)'},
    ];
    byId('cbStats').innerHTML = tiles.map(tl=>`<div class="cb-stat"><div class="cb-stat-val" style="color:${tl.color}">${tl.val}</div><div class="cb-stat-lbl">${tl.lbl}</div></div>`).join('');
    const hdr = `<thead><tr><th>Product</th><th class="r">Qty</th><th class="r">Revenue</th><th class="r" style="color:#C0392B">TOT ${totPercent}%</th><th class="r" style="color:#E67E22">VAT ${Math.round(vatRate*100)}%</th><th class="r" style="color:#5B7FA6">Materials</th><th class="r" style="color:#9B59B6">Branding</th><th class="r" style="color:#C49B3C">Salaries</th><th class="r" style="color:#1D9E75">Dir. Labour</th><th class="r" style="color:#FF6B35">OT Premium</th><th class="r" style="color:#4A9B7F">Facility & Ops</th><th class="r">Net Profit</th><th class="r">NP %</th></tr></thead>`;
    const bodyRows = rows.map(r=>{ const npPct=r.rev>0?(r.np/r.rev*100):0; const col=r.np>=0?'#2E7D32':'var(--qb-red)'; return `<tr><td>${r.name}</td><td class="r">${r.qty}</td><td class="r">${K(r.rev)}</td><td class="r" style="color:#C0392B">${K(r.tot)}</td><td class="r" style="color:#E67E22">${K(r.vat)}</td><td class="r" style="color:#5B7FA6">${K(r.mat)}</td><td class="r" style="color:#9B59B6">${K(r.brand)}</td><td class="r" style="color:#C49B3C">${K(r.sal)}</td><td class="r" style="color:#1D9E75">${K(r.lab)}</td><td class="r" style="color:#FF6B35">${r.otp>0?K(r.otp):'—'}</td><td class="r" style="color:#4A9B7F">${K(r.fac)}</td><td class="r" style="color:${col};font-weight:600">${K(r.np)}</td><td class="r" style="color:${col};font-weight:600">${npPct.toFixed(1)}%</td></tr>`; }).join('');
    const npTotColor = totNP>=0?'#2E7D32':'var(--qb-red)';
    const totRow = `<tr class="tot"><td colspan="2">TOTAL</td><td class="r">${K(totRev)}</td><td class="r" style="color:#C0392B">${K(totTOT)}</td><td class="r" style="color:#E67E22">${K(totVAT)}</td><td class="r" style="color:#5B7FA6">${K(totMat)}</td><td class="r" style="color:#9B59B6">${K(totBrand)}</td><td class="r" style="color:#C49B3C">${K(totSal)}</td><td class="r" style="color:#1D9E75">${K(totLab)}</td><td class="r" style="color:#FF6B35">${totOTP>0?K(totOTP):'—'}</td><td class="r" style="color:#4A9B7F">${K(totFac)}</td><td class="r" style="color:${npTotColor};font-weight:700">${K(totNP)}</td><td class="r" style="color:${npTotColor};font-weight:700">${totRev>0?(totNP/totRev*100).toFixed(1):'0.0'}%</td></tr>`;
    byId('cbTable').innerHTML = hdr + '<tbody>' + bodyRows + totRow + '</tbody>';
  }

  function renderLines(){
    const t = totals();
    byId('lineItems').innerHTML = t.lines.map((l,i)=>`<div class="qi-row"><div><strong>${l.product?.name||'Unknown'}</strong><div style="font-size:11px;color:var(--qb-g3)">${l.channelLabel}${l.branding!=='none'?` • ${l.branding}`:''}${l.rush?' • Rush':''}${l.payment_days>0?` • ${l.payment_days}d`:''}${l.discount_percent>0?` • -${l.discount_percent}%`:''}</div></div><div class="c">${l.qty}</div><div class="r qi-hide">${l.channelLabel}</div><div class="r">${K(l.unit)}</div><div class="r">${K(l.subtotal)}</div><div><button type="button" class="btn-outline" style="padding:2px 7px" data-remove="${i}">x</button></div></div>`).join('') || '<div style="padding:16px;text-align:center;color:var(--qb-g3);font-size:12px">No items added yet.</div>';
    byId('ltSubtotal').textContent=K(t.subtotal); byId('ltDisc').textContent='-'+K(t.docDisc); byId('ltVat').textContent=K(t.vat); byId('ltTotal').textContent=K(t.total);
    byId('marginSummary').innerHTML = t.lines.length ? `<div style="display:flex;justify-content:space-between;padding:4px 0"><span>Cost</span><strong>${K(t.cost)}</strong></div><div style="display:flex;justify-content:space-between;padding:4px 0"><span>Margin</span><strong class="${t.marginPct>=50?'gm-hi':t.marginPct>=30?'gm-mid':'gm-lo'}">${K(t.margin)} (${pc(t.marginPct)})</strong></div><div style="font-size:11px;color:var(--qb-g3);margin-top:8px">Internal only. Not shown on client quote.</div>` : '<div style="color:var(--qb-g3);font-size:12px;text-align:center;padding:10px">Add items to see margin analysis.</div>';
    renderCostBreakdown(t);
    document.querySelectorAll('[data-remove]').forEach(b=>b.addEventListener('click',()=>{ state.lines.splice(+b.dataset.remove,1); renderLines(); }));
  }
  function addLine(){
    const line = normalizeLine({ product_id:byId('product_id').value, quantity:byId('quantity').value, channel:byId('channel').value, branding:document.querySelector('#brandPills .pill.on')?.dataset.value||'none', payment_days:document.querySelector('#payPills .pill.on')?.dataset.value||0, rush:byId('rush').checked, manual_discount_percent:byId('manual_discount').value, strategic_discount_percent:byId('strategic_discount').value||document.querySelector('#stratPills .pill.on')?.dataset.value||0 });
    if(!line.product_id){ alert('Select a product.'); return; }
    const calc = calcLine(line); if(calc?.error){ alert(calc.error); return; }
    state.lines.push(line); renderLines();
  }
  function genSalesQuote(){
    const t=totals(); if(!t.lines.length){ alert('Add items first.'); return; }
    byId('sqNum').textContent = cfg.invoice?.invoice_number || 'DRAFT-QUOTE';
    byId('sqDate').textContent = byId('issue_date').value || '';
    const valid = new Date(byId('issue_date').value || Date.now()); valid.setDate(valid.getDate() + (+byId('valid_days').value||30));
    byId('sqValid').textContent = valid.toLocaleDateString('en-GB',{day:'2-digit',month:'long',year:'numeric'});
    const client = (cfg.clients||[]).find(c=>+c.id===+byId('client_id').value);
    byId('sqClient').innerHTML = client ? `<strong>${client.name}</strong><div>${client.company||''}</div><div>${client.email||''}</div><div>${client.phone||''}</div>` : '<strong>Client</strong>';
    byId('sqItems').innerHTML = t.lines.map(l=>`<tr><td><strong>${l.product.name}</strong><div style="font-size:11px;color:#666">${l.branding!=='none'?`Branding: ${l.branding.replace('_',' ')}`:''}${l.payment_days>0?` • ${l.payment_days}-day terms`:''}${l.discount_percent>0?` • Preferential pricing applied`:''}</div></td><td class="r">${l.qty}</td><td class="r">${l.discount_percent>0?`<div style="text-decoration:line-through;color:#999;font-size:11px">${K(l.base)}</div>`:''}${K(l.unit)}</td><td class="r">${K(l.subtotal)}</td></tr>`).join('');
    byId('sqSubtotal').textContent=K(t.subtotal); byId('sqVat').textContent=K(t.vat); byId('sqTotal').textContent=K(t.total);
    byId('sqLead').textContent = byId('lead_time').value || 'TBD';
    byId('sqNotes').textContent = byId('notes').value || '';
    byId('sqTerms').textContent = byId('terms').value || settings.quote_default_terms || '';
    document.querySelector('[data-pane="p-sales"]').click();
  }

  function addHidden(form, name, value){
    const i=document.createElement('input'); i.type='hidden'; i.name=name; i.value=value==null?'':String(value); form.appendChild(i);
  }
  function saveQuote(){
    const t = totals();
    if(!+byId('client_id').value){ alert('Select a client.'); return; }
    if(!t.lines.length){ alert('Add at least one item.'); return; }
    const form = byId('saveForm');
    form.action = cfg.invoice ? cfg.routes.update : cfg.routes.store;
    form.querySelector('[name=_method]').value = cfg.invoice ? 'PUT' : 'POST';
    form.querySelectorAll('input[data-dyn]').forEach(n=>n.remove());
    const set=(n,v)=>{const i=document.createElement('input');i.type='hidden';i.name=n;i.value=v==null?'':String(v);i.dataset.dyn='1';form.appendChild(i);};
    set('client_id',byId('client_id').value); set('issue_date',byId('issue_date').value); set('valid_days',byId('valid_days').value||numSetting('quote_default_valid_days')); set('due_date',byId('due_date').value);
    set('currency',byId('currency').value||'ZMW'); set('vertical',byId('vertical').value); set('notes',byId('notes').value); set('terms',byId('terms').value);
    set('lead_time',byId('lead_time').value); set('prepared_by',byId('prepared_by').value); set('discount_percent',byId('discount_percent').value||0); set('tax_percent',byId('tax_percent').value||numSetting('quote_vat_percent'));
    state.lines.forEach((l,idx)=>{ ['product_id','quantity','channel','branding','payment_days','rush','manual_discount_percent','strategic_discount_percent'].forEach(k=>set(`items[${idx}][${k}]`, k==='rush' ? (l[k]?1:0) : l[k])); });
    form.submit();
  }

  function bindPills(id, inputId){ document.querySelectorAll(`#${id} .pill`).forEach(p=>p.addEventListener('click',()=>{ document.querySelectorAll(`#${id} .pill`).forEach(x=>x.classList.remove('on')); p.classList.add('on'); if(inputId) byId(inputId).value=p.dataset.value||''; })); }
  document.querySelectorAll('.qb .tab').forEach(t=>t.addEventListener('click',()=>{ document.querySelectorAll('.qb .tab').forEach(x=>x.classList.remove('on')); document.querySelectorAll('.qb .pane').forEach(x=>x.classList.remove('on')); t.classList.add('on'); byId(t.dataset.pane).classList.add('on'); }));
  bindPills('brandPills'); bindPills('payPills'); bindPills('stratPills','strategic_discount');
  byId('addLine').addEventListener('click', addLine); byId('genQuote').addEventListener('click', genSalesQuote); byId('saveQuote').addEventListener('click', saveQuote);
  byId('discount_percent').addEventListener('input', renderLines); byId('tax_percent').addEventListener('input', renderLines);
  renderProducts(); renderOverview(); renderLines();
  byId('issue_date').value = cfg.invoice?.issue_date || new Date().toISOString().slice(0,10);
  byId('valid_days').value = cfg.invoice?.valid_days || numSetting('quote_default_valid_days');
  byId('tax_percent').value = cfg.invoice?.tax_percent ?? numSetting('quote_vat_percent');
  byId('terms').value = cfg.invoice?.terms || (settings.quote_default_terms ?? '');
  if(cfg.invoice){ byId('client_id').value=cfg.invoice.client_id||''; byId('due_date').value=cfg.invoice.due_date||''; byId('currency').value=cfg.invoice.currency||'ZMW'; byId('vertical').value=cfg.invoice.vertical||''; byId('notes').value=cfg.invoice.notes||''; byId('lead_time').value=cfg.invoice.lead_time||''; byId('prepared_by').value=cfg.invoice.prepared_by||''; byId('discount_percent').value=cfg.invoice.discount_percent||0; renderLines(); }
})();
