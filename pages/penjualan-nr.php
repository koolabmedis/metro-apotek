<?php
$subNav = array(
	"Penjualan Bebas ; penjualan-nr.php ; #509601;",
        "Penjualan Resep ; penjualan.php ; #509601;"
);
set_include_path("../");
include_once("inc/essentials.php");
include_once("inc/functions.php");
include_once("models/transaksi.php");
include_once("pages/message.php");
?>

<script type="text/javascript">
$(document).tooltip();
load_data_penjualannr();

//hitung_detail_total(jml, jum, data.diskon_rupiah, data.diskon_persen, data.harga_jual);
function hitung_detail_total(jml, jum, diskon_rupiah, diskon_persen, harga_jual) {
    if (diskon_persen === undefined) {
        dp = '0';
    } else {
        dp = diskon_persen;
    }
    
    if (diskon_rupiah === undefined) {
        dr = '0';
    } else {
        dr = diskon_rupiah;
    }
    $('#hargajual'+jml).html(numberToCurrency(harga_jual));
    $('#diskon_rupiah'+jml).val(numberToCurrency(parseInt(dr)));
    $('#diskon_persen'+jml).val(dp);
    var subtotal = (jum*harga_jual);
    $('#subtotal'+jml).html(numberToCurrency(parseInt(subtotal)));
    hitung_total_penjualan();
}

function hitung_total_penjualan() {
    var panjang   = $('.tr_rows').length; // banyaknya baris data
    var total     = 0;
    for(i = 1; i <= panjang; i++) {
        var subtotal = parseInt(currencyToNumber($('#subtotal'+i).html()));
        total   = total + subtotal;
    }
    var diskon_pr = ($('#diskon_pr').val()/100); // diskon penjualan %
    var diskon_rp = $('#diskon_rp').val(); // diskon penjualan Rp.
    var ppn_jual  = ($('#ppn').val()/100);
    var tuslah    = parseInt(currencyToNumber($('#tuslah').val()));
    if (diskon_pr !== 0) {
        total_terdiskon = total-(total*diskon_pr); // total terdiskon persentase
    } else {
        total_terdiskon = total-diskon_rp; // total terdiskon rupiah
    }
    var total_tambah_ppn = total_terdiskon+(total_terdiskon*ppn_jual);
    var total_tambah_tuslah = total_tambah_ppn+tuslah;
    
    $('#total-penjualan').html(numberToCurrency(parseInt(total_tambah_tuslah)));
}

function add_new_rows(id_brg, nama_brg, jumlah) {
    var jml = $('.tr_rows').length+1;
    
    var str = '<tr class="tr_rows">'+
                '<td align=center>'+jml+'</td>'+
                '<td>&nbsp;'+nama_brg+' <input type=hidden name=id_barang[] value="'+id_brg+'" class=id_barang id=id_barang'+jml+' /></td>'+
                '<td><input type=text name=jumlah[] id=jumlah'+jml+' value="'+jumlah+'" style="text-align: center;" /></td>'+
                '<td><select name=kemasan[] id=kemasan'+jml+'><option value="">Pilih ...</option></select></td>'+
                '<td align=right id=hargajual'+jml+'></td>'+
                '<td><input type=text name=diskon_rupiah[] style="text-align: right;" id=diskon_rupiah'+jml+' value="0" /></td>'+
                '<td><input type=text name=diskon_persen[] style="text-align: center;" id=diskon_persen'+jml+' value="0" /></td>'+
                '<td align=right id=subtotal'+jml+'></td>'+
                '<td align=center><img onclick=removeMe('+jml+'); title="Klik untuk hapus" src="img/icons/delete.png" class=add_kemasan align=left /></td>'+
              '</tr>';
    $('#pesanan-list tbody').append(str);
    $.getJSON('models/autocomplete.php?method=get_kemasan_barang&id='+id_brg, function(data){
        $('#kls_terapi').html('');
        $.each(data, function (index, value) {
            $('#kemasan'+jml).append("<option value='"+value.id+"'>"+value.nama+"</option>");
        });
    });
    $('#ed'+jml).datepicker({
        changeYear: true,
        changeMonth: true,
        minDate: 0
    });
    $('#diskon_rupiah'+jml).blur(function() {
        var jumlah      = $('#jumlah'+jml).val();
        var diskon_rp   = parseInt(currencyToNumber($('#diskon_rupiah'+jml).val()));
        var diskon_pr   = $('#diskon_persen'+jml).val();
        
        
    });
    $('#kemasan'+jml).change(function() {
        var id  = $(this).val();
        var jum = $('#jumlah'+jml).val();
        $.ajax({
            url: 'models/autocomplete.php?method=get_detail_harga_barang&id='+id+'&jumlah='+jum,
            dataType: 'json',
            cache: false,
            success: function(data) {
                hitung_detail_total(jml, jum, data.diskon_rupiah, data.diskon_persen, data.harga_jual);
            }
        });
    });
    $.ajax({
        url: 'models/autocomplete.php?method=get_stok_sisa&id='+id_brg,
        dataType: 'json',
        cache: false,
        success: function(data) {
            if (data.sisa === null) {
                sisa = '0';
            } else {
                sisa = data.sisa;
            }
            $('#sisa'+jml).html(sisa);
        }
    });
}

function form_add() {
    var str = '<div id="form_penjualannr">'+
            '<form id="save_penjualannr">'+
            '<table width=100% class=data-input><tr valign=top><td width=50%><table width=100%>'+
                '<tr><td width=20%>Tanggal:</td><td><?= form_input('tanggal', date("d/m/Y"), 'id=tanggal size=10') ?></td></tr>'+
                '<tr><td>Customer:</td><td><?= form_input('customer', NULL, 'id=customer size=31') ?> <?= form_hidden('id_customer', NULL, 'id=id_customer') ?></td></tr>'+
                '<tr><td>Diskon:</td><td><?= form_input('diskon_pr', '0', 'id=diskon_pr maxlength=5 onblur="hitung_total_penjualan();" size=10') ?> %, Rp. <?= form_input('diskon_rp', '0', 'id=diskon_rp onblur="hitung_total_penjualan();" size=10') ?></td></tr>'+
                '<tr><td>PPN:</td><td><?= form_input('ppn', '0', 'id=ppn size=10 maxlength=5 onblur="hitung_total_penjualan();"') ?></td></tr>'+
                '<tr><td>Biaya Tuslah:</td><td><?= form_input('tuslah', '0', 'id=tuslah onblur=FormNum(this) onkeyup="hitung_total_penjualan();" size=10') ?></td></tr>'+
            '</table></td><td width=50%><table width=100%>'+
                '<tr><td width=20%>Nama Barang:</td><td><?= form_input('barang', NULL, 'id=barang size=40') ?><?= form_hidden('id_barang', NULL, 'id=id_barang') ?></td></tr>'+
                '<tr><td>Jumlah:</td><td><input type=text value="1" size=5 id=pilih /></td></tr>'+
                '<tr><td>TOTAL:</td><td style="font-size: 40px;" id=total-penjualan>0</td></tr>'+
            '</table></td></tr></table>'+
            '<table width=100% cellspacing="0" class="list-data-input" id="pesanan-list"><thead>'+
                '<tr><th width=5%>No.</th>'+
                    '<th width=33%>Nama Barang</th>'+
                    '<th width=10%>Jumlah</th>'+
                    '<th width=10%>Kemasan</th>'+
                    '<th width=10%>Harga Jual</th>'+
                    '<th width=10%>Diskon RP.</th>'+
                    '<th width=10%>Diskon %</th>'+
                    '<th width=10%>Subtotal</th>'+
                    '<th width=2%>#</th>'+
                '</tr></thead>'+
                '<tbody></tbody>'+
            '</table>'+
            '</form></div>';
    $('body').append(str);
    var lebar = $('#pabrik').width();
    $('#pilih').keydown(function(e) {
        if (e.keyCode === 13) {
            var id_barang   = $('#id_barang').val();
            var nama        = $('#barang').val();
            if (id_barang !== '') {
                add_new_rows(id_barang, nama, $(this).val());
            }
            $('#id_barang').val('');
            $('#barang').val('').focus();
        }
    });
    $('#barang').keydown(function(e) {
        if (e.keyCode === 13) {
            $('#pilih').focus().select();
        }
    });
    $('#customer').autocomplete("models/autocomplete.php?method=pasien",
    {
        parse: function(data){
            var parsed = [];
            for (var i=0; i < data.length; i++) {
                parsed[i] = {
                    data: data[i],
                    value: data[i].nama // nama field yang dicari
                };
            }
            $('#id_customer').val('');
            return parsed;
        },
        formatItem: function(data,i,max){
            var str = '<div class=result>'+data.nama+' <br/> '+data.alamat+'</div>';
            return str;
        },
        width: lebar, // panjang tampilan pencarian autocomplete yang akan muncul di bawah textbox pencarian
        dataType: 'json', // tipe data yang diterima oleh library ini disetup sebagai JSON
        cacheLength: 0
    }).result(
    function(event,data,formated){
        $(this).val(data.nama);
        $('#id_customer').val(data.id);
    });
    $('#barang').autocomplete("models/autocomplete.php?method=barang",
    {
        parse: function(data){
            var parsed = [];
            for (var i=0; i < data.length; i++) {
                parsed[i] = {
                    data: data[i],
                    value: data[i].nama // nama field yang dicari
                };
            }
            return parsed;
        },
        formatItem: function(data,i,max){
            var str = '<div class=result>'+data.nama_barang+'</div>';
            return str;
        },
        width: lebar, // panjang tampilan pencarian autocomplete yang akan muncul di bawah textbox pencarian
        dataType: 'json', // tipe data yang diterima oleh library ini disetup sebagai JSON
        cacheLength: 0
    }).result(
    function(event,data,formated){
        $(this).val(data.nama_barang);
        $('#id_barang').val(data.id);
    });
    var wWidth = $(window).width();
    var dWidth = wWidth * 0.6;
    
    var wHeight= $(window).height();
    var dHeight= wHeight * 1;
    $('#form_penjualannr').dialog({
        title: 'Penjualan Bebas',
        autoOpen: true,
        modal: true,
        width: dWidth,
        height: dHeight,
        hide: 'clip',
        show: 'blind',
        buttons: {
            "Simpan": function() {
                $('#save_penjualannr').submit();
            }, 
            "Cancel": function() {    
                $(this).dialog().remove();
            }
        }, close: function() {
            $(this).dialog().remove();
        }, open: function() {
            $('#barang').focus();
        }
    });
    $('#tanggal').datepicker({
        changeYear: true,
        changeMonth: true
    });
    $('#save_penjualannr').submit(function() {
        var jumlah = $('.tr_rows').length;
        
        if (jumlah === 0) {
            alert_empty('Barang', '#barang');
            return false;
        }
        if (jumlah > 0) {
            for (i = 1; i <= jumlah; i++) {
                if ($('#id_barang'+i).val() === '') {
                    alert_empty('Barang','#barang'+i);
                    return false;
                }
                if ($('#nobatch'+i).val() === '') {
                    alert_empty('No. batch','#nobatch'+i);
                    return false;
                }
                if ($('#ed'+i).val() === '') {
                    alert_empty('expired date','#nobatch'+i);
                    return false;
                }
                if ($('#masuk'+i).val() === '' || $('#keluar'+i).val() === '') {
                    alert_empty('masuk & keluar','#masuk'+i);
                    return false;
                }
            }
        }
        $.ajax({
            url: 'models/update-transaksi.php?method=save_penjualannr',
            data: $(this).serialize(),
            dataType: 'json',
            type: 'POST',
            success: function(data) {
                if (data.status === true) {
                    alert_tambah();
                    load_data_penjualannr();
                    $('#pesanan-list tbody').html('');
                }
            }
            
        });
        return false;
    });
}
$mainNav.set("home");
$('#button').button({
    icons: {
        primary: 'ui-icon-newwin'
    }
});
$('#button').click(function() {
    form_add();
});
$('#reset').button({
    icons: {
        primary: 'ui-icon-refresh'
    }
}).click(function() {
    load_data_penjualannr();
});
$.plugin($afterSubPageShow,{ // <-- event is here
    showAlert:function(){ // <-- random function name is here (choose whatever you want)
    /* The code that will be executed */
    }
});
function load_data_penjualannr(page, search, id) {
    pg = page; src = search; id_barg = id;
    if (page === undefined) { var pg = ''; }
    if (search === undefined) { var src = ''; }
    if (id === undefined) { var id_barg = ''; }
    $.ajax({
        url: 'pages/penjualannr-list.php',
        cache: false,
        data: 'page='+pg+'&search='+src+'&id_penjualannr='+id_barg,
        success: function(data) {
            $('#result-penjualannr').html(data);
        }
    });
}

function paging(page, tab, search) {
    load_data_penjualannr(page, search);
}

function edit_penjualannr(str) {
    
    var arr = str.split('#');
    form_add();
    $('#form_add').dialog({ title: 'Edit penjualannr' });
    $('#id_penjualannr').val(arr[0]);
    $('#nama').val(arr[1]);
    if (arr[2] === 'P') { $('#prm').attr('checked','checked'); }
    if (arr[2] === 'L') { $('#l').attr('checked','checked'); }
    $('#alamat').val(arr[3]);
    $('#telp').val(arr[4]);
    $('#email').val(arr[5]);
    $('#nostr').val(arr[6]);
    $('#spesialis').val(arr[7]);
    $('#tglmulai').val(arr[8]);
    
}

function delete_penjualannr(id, page) {
    $('<div id=alert>Anda yakin akan menghapus data ini?</div>').dialog({
        title: 'Konfirmasi Penghapusan',
        autoOpen: true,
        modal: true,
        buttons: {
            "OK": function() {
                $.ajax({
                    url: 'models/update-transaksi.php?method=delete_penjualannr&id='+id,
                    cache: false,
                    success: function() {
                        load_data_penjualannr(page);
                        $('#alert').dialog().remove();
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog().remove();
            }
        }
    });
}
</script>
<h1 class="margin-t-0">Penjualan Bebas</h1>
<hr>
<button id="button">Tambah Data</button>
<button id="reset">Reset</button>
<div id="result-penjualannr">
    
</div>