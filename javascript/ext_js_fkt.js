//<script>

function get_browserpis()
{
    let pis = "";
    for (let i=0; i < navigator.plugins.length; i++) {
        pis += navigator.plugins[i].name + ';' + navigator.plugins[i].filename + ';';
    }
    return pis;
}

//</script>
