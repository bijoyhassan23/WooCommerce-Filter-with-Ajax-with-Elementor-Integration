document.querySelectorAll(".filter_con").forEach(filter => {
    filter.querySelectorAll(".each_filter").forEach(each_filter => {
        const get_clapse_part = each_filter.querySelector(".clapse_able_part");
        each_filter.querySelector(".filter_header").addEventListener("click", function(){
            get_clapse_part.classList.toggle("open");
            this.querySelector(".toggle-icon").textContent = get_clapse_part.classList.contains("open") ? "-" : "+";
        });
    });

    function getFilterData(){
        const formData = new FormData(filter);
        if(!formData.get("s").trim()) formData.delete("s");
        const params = new URLSearchParams(formData);
        const searchString = params.toString();
        return searchString;
    }

    let debounceTimer;

    function applyFilters(e){
        const productGrid = document.querySelector('.wc-filter-loop-grid');
        if(!productGrid) return;

        productGrid.setAttribute("load-status", "true");

        if((e?.target?.name && e.target.name === "min_price") || (e?.target?.name && e.target.name === "max_price")){
            const minPrice = filter.querySelector('input[name="min_price"]').value;
            const maxPrice = filter.querySelector('input[name="max_price"]').value;
            if(minPrice && maxPrice && parseFloat(minPrice) > parseFloat(maxPrice)) return;``
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const filterData = getFilterData();
            const templateId = productGrid?.dataset?.templateId;
            const filterHook = productGrid?.dataset?.filterHook;
            if(templateId){
                let ajaxUrl = `${wcFilterAjax.ajax_url}?action=wc_filter_products&template_id=${templateId}&filter_hook=${filterHook}&${filterData}&nonce=${wcFilterAjax.nonce}`;
                if(wcFilterAjax?.tax_query){
                    Object.entries(wcFilterAjax.tax_query).forEach(([key, value]) => {
                        ajaxUrl += `&tax_query[${key}]=${value}`;
                    });
                }
                fetch(ajaxUrl, {method: "GET"})
                .then(response => response.text())
                .then(data => {
                    if(data.trim().endsWith("0")) data = data.substring(0, data.length - 1);
                    productGrid.innerHTML = data;
                    let newUrl = window.location.pathname.replace(/\/page\/\d+\/?$/, '/');
                    history.replaceState({}, '', `${newUrl}?${filterData}`);
                    productGrid.querySelectorAll(".wc-filter-pagination a").forEach(link => {
                        let giveURL = link.getAttribute("href");
                        link.href = giveURL.replace('/wp-admin/admin-ajax.php/', location.pathname);
                    });
                    productGrid.setAttribute("load-status", "false");
                })
                .catch(error => {
                    console.error("Error applying filters:", error);
                    productGrid.setAttribute("load-status", "false");
                });
            }
        }, 100); // Debounce for 500ms
    }

    // Attach event listeners to all filter inputs
    // filter.querySelectorAll("input, select, textarea").forEach(input => {
    //     input.addEventListener("change", applyFilters);
    //     input.addEventListener("input", applyFilters);
    // }); 
    filter.addEventListener("submit", function(e){
        e.preventDefault();
        applyFilters(e);
    });
    window.filter = filter; // Expose filter to the global scope for debugging
    const resetButton = filter.querySelector(".reset-filters");
    if(resetButton){
        resetButton.addEventListener("click", function(e){
            e.preventDefault();
            filter.querySelectorAll("input, select, textarea").forEach(input => {
                if(input.type === "checkbox" || input.type === "radio"){
                    input.checked = false;
                } else {
                    input.value = "";
                }
            });
            filter.querySelector('[name="per_page"]').checked = true;
            applyFilters(new Event("change"));
        });
    }
});