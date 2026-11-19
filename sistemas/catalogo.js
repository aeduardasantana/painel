(()=>{'use strict';
const input=document.getElementById('pesquisa'),cards=[...document.querySelectorAll('.card')],groups=[...document.querySelectorAll('[data-group]')],counter=document.getElementById('contador'),empty=document.getElementById('sem-resultados');
const normalize=s=>String(s||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim();
if(!input||!counter)return;
const update=()=>{
 const query=normalize(input.value);
 let visible=0;
 for(const card of cards){const match=normalize(card.dataset.search).includes(query);card.hidden=!match;if(match)visible++;}
 for(const group of groups){const has=[...group.querySelectorAll('.card')].some(card=>!card.hidden);group.hidden=!has;if(query&&has&&group.tagName==='DETAILS')group.open=true;}
 counter.textContent=query?(visible+' '+(visible===1?'acesso encontrado':'acessos encontrados')):(visible+' endereços catalogados');
 empty.hidden=visible!==0;
};
input.addEventListener('input',update);
update();
})();