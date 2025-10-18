(function(){
  'use strict';
  if (!window.qz) return;

  // Provide certificate promise – served from our backend
  qz.security.setCertificatePromise(function(resolve, reject){
    fetch('../ajax/qz_cert.php', { cache: 'no-store' })
      .then(function(res){ if(!res.ok) throw new Error('Cert fetch failed'); return res.text(); })
      .then(function(cert){ resolve(cert); })
      .catch(function(err){ reject(err); });
  });

  // Provide signature promise – server signs challenge with private key
  qz.security.setSignaturePromise(function(toSign){
    return function(resolve, reject){
      fetch('../ajax/qz_sign.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ toSign: toSign })
      })
      .then(function(res){ if(!res.ok) throw new Error('Sign failed'); return res.text(); })
      .then(function(sig){ resolve(sig); })
      .catch(function(err){ reject(err); });
    };
  });
})();
