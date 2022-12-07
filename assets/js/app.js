($ => {
    
    $(".search-form .tabs button").on("click", function() {
        let index = $(this).index();
        $(this).addClass("active").siblings("button").removeClass("active");
        $(".search-form .tab-contents .content").removeClass("active").eq(index).addClass("active");
    });

})(jQuery);