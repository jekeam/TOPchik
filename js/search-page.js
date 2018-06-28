function searchPage(val){	
    val = val.toLowerCase();
    
    if (val.length > 0){
        var els = document.getElementsByClassName('page-name');
        for (var x=0; x<els.length; x++){
            subVal = els[x].innerHTML.toLowerCase();
            pageId = els[x].dataset.id;
            isMatch = subVal.indexOf(val);
            if (isMatch>=0){
                document.getElementById('collapse-'+pageId).style.display = 'block';
            }else{
                document.getElementById('collapse-'+pageId).style.display = 'none';
            }
        }        
    }else{
        var els = document.getElementsByClassName('page-name');
        for (var x=0; x<els.length; x++){            
            pageId = els[x].dataset.id;
            document.getElementById('collapse-'+pageId).style.display = 'block';
        }
    }
}