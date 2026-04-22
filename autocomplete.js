// Form logic: datalist threshold + event toggle

(function () {

  // ── Datalist: only attach list= after 2 characters ─────────────
  document.querySelectorAll('input[data-list]').forEach(function (input) {
    var listId = input.getAttribute('data-list');
    input.addEventListener('input', function () {
      if (input.value.length >= 2) {
        input.setAttribute('list', listId);
      } else {
        input.removeAttribute('list');
      }
    });
  });

  // ── Athlete datalist: reload when school changes ────────────────
  var schoolInput  = document.getElementById('schoolInput');
  var athleteDL    = document.getElementById('dl-athletes');

  function loadAthletes(school) {
    if (!school || !athleteDL) return;
    fetch('data.php?api=athlete&query=&school=' + encodeURIComponent(school))
      .then(function (r) { return r.json(); })
      .then(function (items) {
        athleteDL.innerHTML = '';
        items.forEach(function (label) {
          var opt = document.createElement('option');
          opt.value = label;
          athleteDL.appendChild(opt);
        });
      })
      .catch(function () {});
  }

  if (schoolInput) {
    schoolInput.addEventListener('change', function () {
      loadAthletes(schoolInput.value);
    });
    schoolInput.addEventListener('blur', function () {
      loadAthletes(schoolInput.value);
    });
    // Load on page init if school already populated (e.g. after a failed submit)
    if (schoolInput.value) loadAthletes(schoolInput.value);
  }

  // ── Event selection: toggle time/points/athlete ─────────────────
  const eventInput    = document.getElementById('eventInput');
  const timeSelector  = document.getElementById('timeSelector');
  const pointSelector = document.getElementById('pointSelector');
  const athleteWrap   = document.getElementById('athleteAutoComplete');

  function applyEventRules(eventName) {
    const isDiving = eventName.indexOf('Diving') !== -1;
    const isRelay  = eventName.indexOf('Relay')  !== -1;

    if (isDiving) {
      timeSelector.style.display  = 'none';
      pointSelector.style.display = 'block';
      ['minutes', 'seconds', 'millis'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
      });
    } else {
      pointSelector.style.display = 'none';
      timeSelector.style.display  = 'block';
      var pts = document.getElementById('points');
      if (pts) pts.value = '';
    }

    if (athleteWrap) {
      athleteWrap.style.display = isRelay ? 'none' : 'grid';
      if (isRelay) {
        var ath = document.getElementById('athleteInput');
        if (ath) ath.value = '';
      }
    }
  }

  if (eventInput) {
    eventInput.addEventListener('input', function () {
      applyEventRules(eventInput.value);
    });
    eventInput.addEventListener('change', function () {
      applyEventRules(eventInput.value);
    });
  }

})();
