let selectedResultIdx = 0;

document.addEventListener('DOMContentLoaded', initPOS);
document.addEventListener('livewire:navigated', initPOS);

function initPOS() {
    focusBarcode();
    bindBarcodeEvents();
}

function focusBarcode() {
    setTimeout(function() {
        var inp = document.getElementById('barcode-input');
        if (inp) inp.focus();
    }, 50);
}

function bindBarcodeEvents() {
    var inp = document.getElementById('barcode-input');
    if (!inp || inp._bound) return;
    inp._bound = true;

    inp.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            var items = document.querySelectorAll('.sr-item');
            if (items.length > 0) {
                var target = items[selectedResultIdx] || items[0];
                target.click();
            }
            return;
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            var items = document.querySelectorAll('.sr-item');
            if (items.length > 0) {
                selectedResultIdx = Math.min(selectedResultIdx + 1, items.length - 1);
                highlightResult(items);
            }
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            var items = document.querySelectorAll('.sr-item');
            selectedResultIdx = Math.max(selectedResultIdx - 1, 0);
            highlightResult(items);
        }
    });
}

function highlightResult(items) {
    items.forEach(function(el, i) {
        el.classList.toggle('active', i === selectedResultIdx);
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'F12') {
        e.preventDefault();
        var btn = document.getElementById('checkout-btn');
        if (btn && !btn.disabled) btn.click();
    }
    if (e.key === 'F2') {
        e.preventDefault();
        focusBarcode();
    }
    if (e.key === 'Escape') {
        var inp = document.getElementById('barcode-input');
        if (inp) { inp.value = ''; inp.focus(); }
    }
});

function handleGridKey(e, rowIdx, field) {
    if (e.key === 'Tab' || e.key === 'Enter') {
        e.preventDefault();
        if (field === 'qty') {
            var p = document.getElementById('price-' + rowIdx);
            if (p) { p.focus(); p.select(); }
        } else if (field === 'price') {
            var next = document.getElementById('qty-' + (rowIdx + 1));
            if (next) { next.focus(); next.select(); }
            else focusBarcode();
        }
        return;
    }
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        var next = document.getElementById('qty-' + (rowIdx + 1));
        if (next) { next.focus(); next.select(); }
        else focusBarcode();
    }
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (rowIdx === 0) focusBarcode();
        else {
            var prev = document.getElementById('qty-' + (rowIdx - 1));
            if (prev) { prev.focus(); prev.select(); }
        }
    }
}

function changeQty(idx, delta) {
    var inp = document.getElementById('qty-' + idx);
    if (!inp) return;
    var newVal = Math.max(1, (parseFloat(inp.value) || 1) + delta);
    inp.value = newVal;
    updateQty(idx, newVal);
}

function getLivewire() {
    var components = Livewire.all();
    for (var i = 0; i < components.length; i++) {
        var name = components[i].name || components[i].__instance?.name || '';
        if (name.toLowerCase().includes('pos')) {
            return components[i];
        }
    }
    // fallback - اول component
    return components[0] || null;
}
function updateQty(idx, val) {
    var qty = parseFloat(val) || 1;
    var priceInp = document.getElementById('price-' + idx);
    var totalInp = document.getElementById('total-' + idx);
    if (priceInp && totalInp) {
        totalInp.value = (qty * parseFloat(priceInp.value || 0)).toFixed(2);
    }
    var lw = getLivewire();
    if (lw) lw.call('updateQuantity', idx, qty);
}

function updatePriceLocal(idx, val) {
    var price = parseFloat(val) || 0;
    var qtyInp = document.getElementById('qty-' + idx);
    var totalInp = document.getElementById('total-' + idx);
    if (qtyInp && totalInp) {
        totalInp.value = (parseFloat(qtyInp.value || 1) * price).toFixed(2);
    }
    var lw = getLivewire();
    if (lw) lw.call('updatePrice', idx, price);
}

function setAmount(amt) {
    var inp = document.getElementById('cash-received');
    if (inp) inp.value = amt;
    var lw = getLivewire();
    if (lw) lw.set('cashReceived', amt);
}

document.addEventListener('livewire:updated', function() {
    selectedResultIdx = 0;
    var inp = document.getElementById('barcode-input');
    if (inp) inp._bound = false;
    var results = document.getElementById('search-results');
    if (!results) setTimeout(focusBarcode, 100);
    bindBarcodeEvents();
});

function printReceipt(invoiceId) {
    window.open('/receipt/' + invoiceId + '?auto=1', '_blank', 'width=400,height=650');
}