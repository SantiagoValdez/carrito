/*
	VARIABLES GLOBALES
 */
/*
	FUNCIONES AUXILIARES
 */
function formatMoney(num) {
    var p = num.toString();
    var chars = p.split("").reverse();
    var newstr = '';
    var count = 0;
    for (x in chars) {
        count++;
        if(count%3 == 1 && count != 1) {
            newstr = chars[x] + '.' + newstr;
        } else {
            newstr = chars[x] + newstr;
        }
    }
    return newstr;
 }

 $('#sl2').slider()
  .on('slide', function(ev){
  	 var rango = ev.value;
  	 filtroDesde = rango[0];
  	 filtroHasta = rango[1];  	
  	 $("#val-desde").val("" + formatMoney(rango[0]));
  	 $("#val-hasta").val("" + formatMoney(rango[1]));
     console.log(ev.value);
  });

