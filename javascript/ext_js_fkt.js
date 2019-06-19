<script language=javascript>
function get_browserpis() {
   var i,pis;
   for(i=0;i < navigator.plugins.length;i++) {
      pis += navigator.plugins[i].name + ';' + navigator.plugins[i].filename + ';'; 
   }
   return pis;
}
</script>