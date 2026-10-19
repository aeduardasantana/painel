/** Selo G Certificador — MVP administrativo.
 * Execute como: USUÁRIO QUE ACESSA O APP. Acesso: contas Google autorizadas.
 * Propriedades: SELOG_SHEET_ID; SELOG_DRIVE_FOLDER_ID; SELOG_ADMINS (emails separados por vírgula).
 * Não implementar execução como proprietário com acesso anônimo.
 */
const HEAD=['id','criado_em','cnpj','razao_social','tipo','nivel','escopo','validade','status','aprovado_por','aprovado_em','codigo','pdf_id','emitido_em'];
const TYPES=['SELO','CERTIFICADO','CREDENCIAMENTO'];const LEVELS=['BRONZE','PRATA','OURO'];
function auth_(){
 const email=(Session.getActiveUser().getEmail()||'').trim().toLowerCase();
 const admins=(PropertiesService.getScriptProperties().getProperty('SELOG_ADMINS')||'').split(',').map(x=>x.trim().toLowerCase()).filter(Boolean);
 if(!email||!admins.includes(email))throw Error('Acesso não autorizado. Verifique a conta Google autorizada.');
 return email;
}
function sheet_(){const id=PropertiesService.getScriptProperties().getProperty('SELOG_SHEET_ID');if(!id)throw Error('Configure SELOG_SHEET_ID');const book=SpreadsheetApp.openById(id);let s=book.getSheetByName('SOLICITACOES');if(!s){s=book.insertSheet('SOLICITACOES');s.appendRow(HEAD)}if(s.getLastRow()>0&&HEAD.some((h,i)=>String(s.getRange(1,i+1).getValue())!==h))throw Error('Cabeçalho não corresponde à estrutura esperada.');return s;}
function doGet(){auth_();return HtmlService.createHtmlOutputFromFile('Index').setTitle('Certificador | Selo G').addMetaTag('viewport','width=device-width, initial-scale=1');}
function newRequest(data){
 const user=auth_();const c=String(data.cnpj||'').replace(/\D/g,'');const razao=String(data.razao||'').trim();const tipo=String(data.tipo||'').toUpperCase();const nivel=String(data.nivel||'').toUpperCase();const escopo=String(data.escopo||'').trim();const validade=String(data.validade||'');
 if(c.length!==14||razao.length<3||razao.length>180||!TYPES.includes(tipo)||escopo.length<3||escopo.length>240||!/\d{4}-\d{2}-\d{2}/.test(validade)||isNaN(Date.parse(validade+'T12:00:00'))||Date.parse(validade+'T12:00:00')<=Date.now())throw Error('Confira CNPJ, dados e data futura de validade.');
 if(tipo==='SELO'&&!LEVELS.includes(nivel))throw Error('Nível obrigatório para selo.');
 const id=Utilities.getUuid(),now=new Date();const s=sheet_();const row=[id,now,c,razao,tipo,tipo==='SELO'?nivel:'',escopo,validade,'EM_ANALISE','','','','',''];
 const lock=LockService.getScriptLock();lock.waitLock(15000);try{s.appendRow(row);}finally{lock.releaseLock()}return {id,status:'EM_ANALISE'};
}
function listRequests(){auth_();const rows=sheet_().getDataRange().getValues();return rows.slice(1).reverse().slice(0,100).map(r=>({id:String(r[0]),empresa:String(r[3]),tipo:String(r[4]),nivel:String(r[5]),status:String(r[8]),codigo:String(r[11]||'')}));}
function approve(id){const user=auth_(),lock=LockService.getScriptLock();lock.waitLock(15000);try{const s=sheet_(),rows=s.getDataRange().getValues(),ix=rows.findIndex((r,i)=>i>0&&r[0]===id);if(ix<1)throw Error('Requerimento não encontrado');if(rows[ix][8]!=='EM_ANALISE')throw Error('Somente requerimentos em análise podem ser aprovados.');s.getRange(ix+1,9,1,3).setValues([['APROVADO',user,new Date()]]);return {status:'APROVADO'};}finally{lock.releaseLock()}}
function issue(id){
 auth_();const lock=LockService.getScriptLock();lock.waitLock(30000);
 try{
 const s=sheet_(),rows=s.getDataRange().getValues(),ix=rows.findIndex((r,i)=>i>0&&r[0]===id);
 if(ix<1)throw Error('Requerimento não encontrado');const r=rows[ix];if(r[8]!=='APROVADO')throw Error('Somente aprovações registradas podem ser emitidas.');
 const folderId=PropertiesService.getScriptProperties().getProperty('SELOG_DRIVE_FOLDER_ID');if(!folderId)throw Error('Configure SELOG_DRIVE_FOLDER_ID');
 const code='SG-'+new Date().getFullYear()+'-'+Utilities.getUuid().replace(/-/g,'').slice(0,16).toUpperCase();
 const verification='https://selog.compassrosesystems.com.br/validar/?codigo='+encodeURIComponent(code);
 const doc=DocumentApp.create('Selo G | '+code),body=doc.getBody();body.appendParagraph('SELO G').setHeading(DocumentApp.ParagraphHeading.TITLE);
 body.appendParagraph('CERTIFICADO INSTITUCIONAL DE RECONHECIMENTO').setHeading(DocumentApp.ParagraphHeading.HEADING2);
 body.appendParagraph('Organização: '+r[3]);body.appendParagraph('CNPJ: '+r[2]);body.appendParagraph('Instrumento: '+r[4]);if(r[5])body.appendParagraph('Nível: '+r[5]);body.appendParagraph('Escopo: '+r[6]);
 body.appendParagraph('Validade até: '+r[7]);body.appendParagraph('Código único: '+code);body.appendParagraph('Verificação: '+verification);
 body.appendParagraph('Reconhecimento privado. Não constitui certificação governamental nem comprovação automática de conformidade legal.');
 doc.saveAndClose();const folder=DriveApp.getFolderById(folderId);const pdf=folder.createFile(DriveApp.getFileById(doc.getId()).getAs(MimeType.PDF).setName('SELOG-'+code+'.pdf'));DriveApp.getFileById(doc.getId()).setTrashed(true);
 s.getRange(ix+1,9).setValue('EMITIDO');s.getRange(ix+1,12,1,3).setValues([[code,pdf.getId(),new Date()]]);
 return {status:'EMITIDO',codigo:code,pdfId:pdf.getId()};
 }finally{lock.releaseLock()}
}
