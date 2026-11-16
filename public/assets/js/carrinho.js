(function () {
    var KEY = 'gbCarrinho';
    var cfg = window.GB_CARRINHO || {};
    var itens = [];

    function carregar() {
        try {
            var raw = localStorage.getItem(KEY);
            var dados = raw ? JSON.parse(raw) : [];
            itens = Array.isArray(dados) ? dados.filter(function (i) { return i && i.id && i.qtd > 0; }) : [];
        } catch (e) { itens = []; }
    }
    function salvar() {
        try { localStorage.setItem(KEY, JSON.stringify(itens)); } catch (e) {}
    }
    function brl(v) {
        return 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function total() {
        return itens.reduce(function (t, i) { return t + i.preco * i.qtd; }, 0);
    }
    function quantidade() {
        return itens.reduce(function (t, i) { return t + i.qtd; }, 0);
    }

    function render() {
        var corpo = document.getElementById('cartBody');
        var badge = document.getElementById('cartCount');
        var qtd = quantidade();
        badge.textContent = qtd;
        badge.classList.toggle('is-empty', qtd === 0);

        document.getElementById('cartTotal').textContent = brl(total());
        document.getElementById('cartPix').textContent = qtd ? 'No Pix (10% off): ' + brl(total() * 0.9) : '';
        document.getElementById('cartFinish').disabled = qtd === 0 || !cfg.numero;
        document.getElementById('cartClear').style.display = qtd ? 'block' : 'none';
        document.getElementById('cartWarn').style.display = (qtd && !cfg.numero) ? 'block' : 'none';

        if (!itens.length) {
            corpo.innerHTML = '<div class="cart-empty"><strong>Seu carrinho está vazio</strong>Adicione motos do catálogo para montar seu orçamento.</div>';
            return;
        }
        corpo.innerHTML = itens.map(function (i) {
            return '<div class="cart-item" data-id="' + esc(i.id) + '">' +
                '<img src="' + esc(i.img) + '" alt="">' +
                '<div><div class="cart-item-nome">' + esc(i.nome) + '</div>' +
                '<div class="cart-item-cod">' + esc(i.codigo) + '</div>' +
                '<div class="cart-item-preco">' + brl(i.preco * i.qtd) + '</div></div>' +
                '<div class="cart-item-side">' +
                '<div class="cart-qty"><button type="button" data-act="menos" aria-label="Diminuir">−</button><span>' + i.qtd + '</span><button type="button" data-act="mais" aria-label="Aumentar">+</button></div>' +
                '<button type="button" class="cart-remove" data-act="remover">Remover</button></div></div>';
        }).join('');
    }

    function abrir() {
        document.getElementById('cartOverlay').classList.add('open');
        document.getElementById('cartDrawer').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function fechar() {
        document.getElementById('cartOverlay').classList.remove('open');
        document.getElementById('cartDrawer').classList.remove('open');
        document.body.style.overflow = '';
    }

    function adicionar(d, botao) {
        var existente = itens.filter(function (i) { return i.id === d.id; })[0];
        if (existente) { existente.qtd = Math.min(existente.qtd + 1, 9); }
        else { itens.push({ id: d.id, nome: d.nome, preco: parseFloat(d.preco) || 0, codigo: d.codigo, img: d.img, qtd: 1 }); }
        salvar();
        render();
        var badge = document.getElementById('cartCount');
        badge.classList.remove('bump'); void badge.offsetWidth; badge.classList.add('bump');
        if (botao) {
            var txt = botao.querySelector('.btn-cart-txt');
            botao.classList.add('added');
            if (txt) txt.textContent = 'Adicionado ✓';
            setTimeout(function () {
                botao.classList.remove('added');
                if (txt) txt.textContent = 'Adicionar ao carrinho';
            }, 1200);
        }
    }

    function finalizar() {
        if (!itens.length || !cfg.numero) return;
        var linhas = itens.map(function (i) {
            return '• ' + i.qtd + 'x ' + i.nome + ' (' + i.codigo + ') - ' + brl(i.preco * i.qtd);
        });
        var msg = 'Olá! Gostaria de um orçamento para os itens do meu carrinho:\n\n' + linhas.join('\n') +
            '\n\nTotal: ' + brl(total()) + '\nNo Pix (10% off): ' + brl(total() * 0.9);
        var numero = String(cfg.numero).replace(/\D/g, '');
        window.open('https://wa.me/55' + numero + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
    }

    document.addEventListener('DOMContentLoaded', function () {
        carregar();
        render();

        document.addEventListener('click', function (e) {
            var add = e.target.closest('.btn-cart');
            if (add) { adicionar(add.dataset, add); return; }
            if (e.target.closest('#cartToggle')) { abrir(); return; }
            if (e.target.closest('#cartClose') || e.target.id === 'cartOverlay') { fechar(); return; }
            if (e.target.closest('#cartFinish')) { finalizar(); return; }
            if (e.target.closest('#cartClear')) { itens = []; salvar(); render(); return; }

            var acao = e.target.closest('[data-act]');
            var linha = e.target.closest('.cart-item');
            if (acao && linha) {
                var id = linha.dataset.id;
                var item = itens.filter(function (i) { return i.id === id; })[0];
                if (!item) return;
                if (acao.dataset.act === 'mais') item.qtd = Math.min(item.qtd + 1, 9);
                else if (acao.dataset.act === 'menos') item.qtd -= 1;
                else item.qtd = 0;
                itens = itens.filter(function (i) { return i.qtd > 0; });
                salvar(); render();
            }
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fechar(); });
        window.addEventListener('storage', function (e) { if (e.key === KEY) { carregar(); render(); } });
    });
})();
