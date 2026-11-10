(()=>{
const norm=s=>String(s||"").normalize("NFD").replace(/[\u0300-\u036f]/g,"").toLowerCase().trim();
document.querySelectorAll("[data-catalog]").forEach(area=>{
 const search=area.querySelector("[data-search-input]");
 const type=area.querySelector("[data-type-input]");
 const status=area.querySelector("[data-status-input]");
 const nodes=[...area.querySelectorAll("[data-item]")];
 const count=area.querySelector("[data-result-count]");
 const empty=area.querySelector("[data-empty]");
 const run=()=>{
  const q=norm(search?.value),t=type?.value||"todos",s=status?.value||"todos";
  let visible=0;
  nodes.forEach(n=>{
   const ok=(!q||norm(n.dataset.search||n.textContent).includes(q))&&(t==="todos"||n.dataset.type===t)&&(s==="todos"||n.dataset.status===s);
   n.hidden=!ok;if(ok)visible++;
  });
  if(count)count.textContent=visible+" de "+nodes.length+" "+(nodes.length===1?"item":"itens")+" encontrados";
  if(empty)empty.hidden=visible!==0;
 };
 [search,type,status].filter(Boolean).forEach(c=>c.addEventListener(c.tagName==="INPUT"?"input":"change",run));
 run();
});
})();