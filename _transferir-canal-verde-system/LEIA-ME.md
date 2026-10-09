# Transferência para `canal-verde-system` — propostas LONGI / N-TYPE

Conteúdo das mudanças feitas em `Gerador-PDF2` (commits `3c7d30c` → `ea58352`)
que precisam ir para o repositório onde os geradores realmente rodam.

---

## 1. Arquivo NOVO — obrigatório

**`pdf_helpers.php`** (288 linhas)

Sem ele os dois geradores quebram no primeiro `require_once`. Copiar inteiro,
na mesma pasta dos `gerar_pdf_*.php`.

Contém 7 funções de desenho compartilhadas pelas duas modalidades:

| Função | O que faz |
|---|---|
| `escreverCentralizado` | texto centrado em torno de um X |
| `escreverAjustado` | texto à esquerda, encolhendo a fonte até caber numa largura |
| `escreverCentralizadoNaCaixa` | centrado + encolhendo |
| `escreverComposicao` | desenha a lista de itens dentro do retângulo da arte, ajustando entrelinha e um corpo de fonte ÚNICO para o bloco |
| `escreverBlocoTecnologia` | um bloco de tecnologia inteiro (lista + à vista + à prazo + payback) |
| `ehLinhaDePainel` | identifica a linha do painel na composição |
| `trocarPainelNaComposicao` | troca só a linha do painel, preservando a quantidade |

Nos dois geradores foi adicionada, logo após o `require` do autoload:

```php
// Funções de desenho compartilhadas pelas duas modalidades de proposta.
require_once __DIR__ . '/pdf_helpers.php';
```

---

## 2. Artes (PNG)

| Arquivo | Situação |
|---|---|
| `PGINV14.png` | **novo** — estudo de viabilidade + composição (investimento) |
| `PGINV13.png` | **novo** — garantia e degradação (investimento) |
| `PGAUT7.png` | **novo** — análise financeira + garantia (autoconsumo) |
| `PGAUT5.png` | **substituído** — agora é o layout de viabilidade/composição |
| `PGINV6.png` | **substituído** — arte sem as caixas de preço |

`PGAUT5.png` e `PGINV14.png` têm layout idêntico (conferido pixel a pixel);
por isso os dois geradores usam as mesmas coordenadas.

---

## 3. `gerar_pdf_inv_2.php` (investimento)

- Página `PGINV5` → **`PGINV14`**.
- Nova página **`PGINV13`** logo depois da PGINV14 (arte 100% estática, nada é escrito por cima).
- A página PGINV14 desenha **duas tecnologias**: LONGI e N-TYPE.
- Comentários de página passaram a nomear a arte (`// Página PGINV14 — ...`) em vez do ordinal, que estava dessincronizado.
- Removida a referência a `$textoPadrao`, variável que **nunca existiu** nesse arquivo (imprimia linha vazia + notice).
- Ordem final: PGINV1, 2, 3, **14**, **13**, 6, 7, 9, 10, 11, 12 — 11 páginas.

---

## 4. `gerar_pdf_2.php` (autoconsumo)

- Página `PGAUT5` reorganizada no mesmo formato da PGINV14 (duas tecnologias).
- Página `PGAUT6` → **`PGAUT7`**.
- Coluna "Marca" da tabela de garantia da PGAUT7 passou a ser escrita por código
  (na arte ela vem vazia).
- Bloco de preço da PGAUT7 subiu ~5 mm: a seção de garantia subiu na arte nova e
  a faixa livre caiu de ~23 mm para 15 mm (y 114→129). Nas alturas antigas o
  preço invadia o título "Garantia e degradação".
- Payback passou a ser exibido (já era calculado e nunca aparecia).
- Ordem final: PGAUT1, PGINV2, PGINV3, **PGAUT5**, **PGAUT7**, PGINV9, PGINV11, PGINV12 — 8 páginas.

---

## 5. Como as duas tecnologias funcionam

Em cada gerador existe **um único ponto de ligação**, marcado com
`PONTO ÚNICO DE LIGAÇÃO DAS TECNOLOGIAS`:

```php
$dadosLongi = [
    'itens'       => trocarPainelNaComposicao($itensComposicao, $descricaoPainelLongi),
    'viabilidade' => $viabValoresLongi,
    'vista'       => $precoFinalRs,
    'prazo'       => $linhasPrazo,   // LISTA de linhas
    'payback'     => $paybackTexto,
];

// TODO(N-TYPE): trocar pelos valores próprios quando o formulário enviá-los.
$dadosNtype = $dadosLongi;
$dadosNtype['itens'] = trocarPainelNaComposicao($itensComposicao, $descricaoPainelNtype);
```

- **`prazo` é uma lista**: hoje o investimento manda 3 parcelas (36x/48x/60x) e o
  autoconsumo manda o valor único de `$precoFinalPrazo`. Quando virar valor único
  nos dois, basta deixar um item só — o desenho já aceita os dois formatos e
  aumenta a fonte sozinho quando é uma linha só.
- **Painel por tecnologia** já é definitivo:
  - LONGI → `LONGI`
  - N-TYPE → `ZNSHINE / RONMA / OSDA / WEG`
  - faixa de potência exibida: `600-625 W` (texto de catálogo; o cálculo da
    quantidade de módulos continua usando `$potenciaModulo`, o número real)

---

## ⚠️ 6. Pontos em aberto — ler antes de publicar

1. **O bloco N-TYPE é CÓPIA do LONGI** em preço à vista, à prazo, payback e na
   coluna da tabela de viabilidade. Só o painel difere. Enquanto isso durar, a
   proposta mostra o **mesmo preço nas duas tecnologias**, e o cliente vê.

2. **Coluna "Marca" trocada.** Em `PGINV13.png` (dentro da arte) e em
   `gerar_pdf_2.php` (por código, conforme especificado):
   - Inversor → ZNSHINE, RONMA, OSDA, LONGI, WEG → *são fabricantes de módulo*
   - Módulo FV → CHINT, GROWATT, SAJ, SOFAR, WEG → *são fabricantes de inversor*

   As duas propostas estão **consistentes entre si**. Se for corrigir, corrigir nas duas.

3. **Nada disso foi executado.** Não havia PHP na máquina onde as alterações
   foram feitas, então **não houve `php -l` nem PDF de teste**. O que foi
   validado: balanceamento de delimitadores, contagem de páginas
   (`AddPage` x `Image`), encoding UTF-8/CRLF, testes da lógica de troca de
   painel (portada para Node) e prévias visuais renderizando os textos nas
   coordenadas exatas sobre as artes.

   **Rodar `php -l` nos 3 arquivos e gerar um PDF de teste das duas modalidades
   antes de subir.**

4. **Variáveis mortas** em `gerar_pdf_inv_2.php`: `$adicionalAPlus` e
   `$adicionalIndicacao` são lidas do POST e nunca mais usadas (sobraram da
   remoção do bloco de preço da PGINV6).

5. **Segurança, fora do escopo mas visto no caminho:** `buscar_preco_inv.php`
   tem host, usuário e senha do banco de produção em texto puro, versionados.

---

## 7. Como aplicar

Opção A — aplicar o patch:
```
git apply 01-mudancas-php.patch
```

Opção B — copiar os arquivos prontos desta pasta por cima
(`pdf_helpers.php`, `gerar_pdf_2.php`, `gerar_pdf_inv_2.php`) + os 5 PNGs.

Use a opção B se o `canal-verde-system` já tiver divergido desses arquivos —
nesse caso vale comparar antes, porque o patch pode não aplicar limpo.
