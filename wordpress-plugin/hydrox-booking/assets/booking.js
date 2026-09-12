(() => {
  const root = document.getElementById('hydrox-booking');
  if (!root || !window.HydroxBooking) return;
  const form = root.querySelector('form');
  const steps = [...root.querySelectorAll('[data-step]')];
  const progress = [...root.querySelectorAll('[data-progress]')];
  const back = root.querySelector('.hydrox-booking__back');
  const next = root.querySelector('.hydrox-booking__next');
  const submit = root.querySelector('.hydrox-booking__submit');
  const error = root.querySelector('.hydrox-booking__error');
  const photoInput = root.querySelector('#hydrox-photos');
  const previews = root.querySelector('.hydrox-booking__previews');
  const photoSummary = root.querySelector('.hydrox-booking__photo-summary span');
  const uploadStatus = root.querySelector('.hydrox-booking__upload-status');
  let step = 1, photos = [], session = '', reference = '', busy = false, conversionTracked = false;

  const trackGoogleAdsConversion = () => {
    if (conversionTracked || typeof window.gtag !== 'function') return;
    window.gtag('event', 'conversion', {
      send_to: 'AW-18428986459/bOSgCPbm6-0cENu10NNE',
      value: 1.0,
      currency: 'AUD'
    });
    conversionTracked = true;
  };

  const showError = message => { error.textContent = message; error.hidden = false; error.scrollIntoView({behavior:'smooth', block:'center'}); };
  const clearError = () => { error.hidden = true; error.textContent = ''; };
  const setStep = value => {
    step = value; clearError();
    steps.forEach(el => el.classList.toggle('is-active', Number(el.dataset.step) === step));
    progress.forEach(el => { const n=Number(el.dataset.progress); el.classList.toggle('is-active',n===step); el.classList.toggle('is-done',n<step); });
    back.hidden = step === 1; next.hidden = step === 4; submit.hidden = step !== 4;
    back.style.setProperty('display', step === 1 ? 'none' : 'inline-flex', 'important');
    next.style.setProperty('display', step === 4 ? 'none' : 'inline-flex', 'important');
    submit.style.setProperty('display', step === 4 ? 'inline-flex' : 'none', 'important');
    if (step === 4) updateReview();
    root.scrollIntoView({behavior:'smooth', block:'start'});
  };
  const selected = name => [...form.querySelectorAll(`[name="${name}"]:checked`)].map(el => el.value);
  const validateStep = () => {
    if (step === 1 && !selected('services[]').length) return showError('Select at least one main service.'), false;
    if (step === 1 && !form.frequency.value) return showError('Choose how often you need the service.'), false;
    if (step === 2) {
      for (const name of ['address','suburb','postcode']) if (!form[name].checkValidity()) return form[name].reportValidity(), false;
      if (!form.schedule_flexible.checked && (!form.preferred_date.value || !form.preferred_time.value)) return showError('Choose a preferred date and time, or select flexible.'), false;
    }
    return true;
  };
  next.addEventListener('click', () => { if (validateStep()) setStep(step + 1); });
  back.addEventListener('click', () => setStep(step - 1));
  form.schedule_flexible.addEventListener('change', () => root.querySelector('.hydrox-booking__schedule').classList.toggle('is-hidden', form.schedule_flexible.checked));

  const updateReview = () => {
    const serviceText = selected('services[]').join(', ');
    const extrasText = selected('extras[]').join(', ') || 'None';
    const timing = form.schedule_flexible.checked ? 'Flexible' : `${form.preferred_date.value} · ${form.preferred_time.value}`;
    root.querySelector('.hydrox-booking__review').innerHTML = `<b>Please review</b><br><strong>Services:</strong> ${escapeHtml(serviceText)}<br><strong>Extras:</strong> ${escapeHtml(extrasText)}<br><strong>Frequency:</strong> ${escapeHtml(form.frequency.options[form.frequency.selectedIndex].text)}<br><strong>Location:</strong> ${escapeHtml([form.address.value,form.suburb.value,form.postcode.value].filter(Boolean).join(', '))}<br><strong>Timing:</strong> ${escapeHtml(timing)}<br><strong>Photos:</strong> ${photos.length}`;
  };
  const escapeHtml = value => { const d=document.createElement('div'); d.textContent=value; return d.innerHTML; };

  photoInput.addEventListener('change', async event => {
    clearError();
    const incoming = [...event.target.files];
    if (photos.length + incoming.length > HydroxBooking.maxPhotos) { showError(`You can add up to ${HydroxBooking.maxPhotos} photos.`); photoInput.value=''; return; }
    for (const file of incoming) {
      try { photos.push({file: await preparePhoto(file), status:'ready'}); }
      catch (e) { showError(e.message); }
    }
    photoInput.value=''; renderPhotos();
  });
  root.querySelector('.hydrox-booking__clear').addEventListener('click', () => { photos=[]; renderPhotos(); });
  const renderPhotos = () => {
    previews.innerHTML=''; photoSummary.textContent = photos.length ? `${photos.length} of ${HydroxBooking.maxPhotos} photos selected` : 'No photos selected';
    photos.forEach((item,index) => {
      const card=document.createElement('div'); card.className='hydrox-booking__preview';
      const img=document.createElement('img'); img.alt=`Selected photo ${index+1}`; img.src=URL.createObjectURL(item.file);
      img.onload=()=>URL.revokeObjectURL(img.src);
      const remove=document.createElement('button'); remove.type='button'; remove.textContent='×'; remove.setAttribute('aria-label','Remove photo');
      remove.onclick=()=>{ if(!busy){photos.splice(index,1);renderPhotos();} };
      const status=document.createElement('em'); status.textContent=item.status==='uploaded'?'Uploaded':item.status==='failed'?'Retry':'Ready';
      card.append(img,remove,status); previews.append(card);
    });
  };
  const preparePhoto = file => new Promise((resolve,reject) => {
    if (!file.type.startsWith('image/')) return reject(new Error(`${file.name} is not a supported photo.`));
    if (file.size <= HydroxBooking.maxPhotoBytes) return resolve(file);
    const image=new Image(), url=URL.createObjectURL(file);
    image.onload=()=>{
      URL.revokeObjectURL(url); const scale=Math.min(1,2560/Math.max(image.width,image.height));
      const canvas=document.createElement('canvas'); canvas.width=Math.round(image.width*scale); canvas.height=Math.round(image.height*scale);
      canvas.getContext('2d').drawImage(image,0,0,canvas.width,canvas.height);
      canvas.toBlob(blob => {
        if (!blob || blob.size > HydroxBooking.maxPhotoBytes) return reject(new Error(`${file.name} could not be reduced below 10 MB.`));
        resolve(new File([blob], file.name.replace(/\.[^.]+$/,'.jpg'), {type:'image/jpeg'}));
      },'image/jpeg',.82);
    };
    image.onerror=()=>{URL.revokeObjectURL(url);reject(new Error(`${file.name} is over 10 MB and could not be compressed.`));}; image.src=url;
  });

  const ajax = async data => {
    data.append('nonce',HydroxBooking.nonce);
    const response=await fetch(HydroxBooking.ajaxUrl,{method:'POST',body:data,credentials:'same-origin'});
    const json=await response.json().catch(()=>null);
    if(!response.ok || !json?.success) throw new Error(json?.data?.message || 'The request could not be completed. Please try again.');
    return json.data;
  };
  form.addEventListener('submit', async event => {
    event.preventDefault(); clearError();
    if (busy) return;
    for (const name of ['customer_name','phone','email']) if(!form[name].checkValidity()) return form[name].reportValidity();
    const bookingData = new FormData(form);
    busy=true; [...form.elements].forEach(el=>el.disabled=true); uploadStatus.hidden=false; root.querySelector('.hydrox-booking__actions').hidden=true;
    try {
      if(!session){
        bookingData.set('action','hydrox_booking_create');
        const created=await ajax(bookingData); session=created.session; reference=created.reference;
      }
      const pending=photos.filter(p=>p.status!=='uploaded');
      for(let i=0;i<pending.length;i++){
        const item=pending[i], data=new FormData(); data.set('action','hydrox_booking_upload_photo'); data.set('session',session); data.set('photo',item.file,item.file.name);
        try { await ajax(data); item.status='uploaded'; }
        catch(e){ item.status='failed'; renderPhotos(); throw new Error(`Photo ${photos.indexOf(item)+1} failed. ${e.message} Select “Send booking request” to retry.`); }
        const complete=photos.filter(p=>p.status==='uploaded').length;
        uploadStatus.querySelector('span').style.width=`${Math.max(8,Math.round((complete/Math.max(photos.length,1))*90))}%`;
        uploadStatus.querySelector('p').textContent=`Uploading photo ${complete} of ${photos.length}…`; renderPhotos();
      }
      const finalData=new FormData(); finalData.set('action','hydrox_booking_finalize'); finalData.set('session',session);
      const final=await ajax(finalData); uploadStatus.querySelector('span').style.width='100%';
      form.hidden=true; root.querySelector('.hydrox-booking__progress').hidden=true;
      const success=root.querySelector('.hydrox-booking__success'); success.hidden=false;
      success.querySelector('strong').textContent=final.reference || reference;
      success.querySelector('.hydrox-booking__success-copy').textContent=HydroxBooking.successMessage;
      trackGoogleAdsConversion();
      success.scrollIntoView({behavior:'smooth',block:'center'});
    } catch(e) {
      showError(e.message); uploadStatus.hidden=true; root.querySelector('.hydrox-booking__actions').hidden=false;
      [...form.elements].forEach(el=>el.disabled=false); submit.textContent='Retry booking request';
    } finally { busy=false; }
  });
})();
