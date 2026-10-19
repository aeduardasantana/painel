# Certificador Selo G — módulo administrativo inicial

Módulo no repositório central `painel`, implementado como Google Apps Script. Não é uma página estática publicamente acessível.

## Instalação

1. Crie projeto Apps Script e copie `Code.gs` e `Index.html`.
2. Crie planilha privada para registros e pasta privada para PDFs.
3. Nas propriedades do projeto cadastre `SELOG_SHEET_ID`, `SELOG_DRIVE_FOLDER_ID`, `SELOG_ADMINS` (lista de endereços Google autorizados).
4. Implante Web App **executando como usuário que acessa**, apenas para usuários autenticados permitidos. Nunca implante como proprietário com acesso anônimo. Permissões Drive/Sheets dos autorizados são necessárias.
5. Teste autorização, fluxo de cadastro, aprovação, emissão e acesso aos arquivos.

## Estado funcional

Cadastro de requerimento, aprovação manual registrada, emissão de PDF inicial, código aleatório, armazenamento privado e listagem limitada dos 100 mais recentes.

**Limites deliberados:** o PDF é um modelo técnico preliminar, não o certificado final aprovado; QR Code e arte oficial Bronze/Prata/Ouro ainda precisam ser integrados. Consulta pública e download público não estão conectados. Não iniciar concessões reais antes dos testes e do regulamento final. Próximas camadas: identidade gráfica aprovada, QR Code gerado localmente, trilha de auditoria imutável, registro público somente de emissões autorizadas, suspensão/revogação/renovação e regras de publicação.
