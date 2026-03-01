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
        const params = new URLSearchParams(formData);
        const searchString = params.toString();
        return searchString;
    }

    let debounceTimer;

    function applyFilters(e){
        console.log("Filter changed:", e.target.name, e.target.value);
        if(e.target.name === "min_price" || e.target.name === "max_price"){
            const minPrice = filter.querySelector('input[name="min_price"]').value;
            const maxPrice = filter.querySelector('input[name="max_price"]').value;
            if(minPrice && maxPrice && parseFloat(minPrice) > parseFloat(maxPrice)) return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const filterData = getFilterData();
            templateId = document.querySelector('.wc-filter-loop-grid')?.dataset?.templateId;
            if(templateId){
                fetch(`${wcFilterAjax.ajax_url}?action=wc_filter_products&template_id=${templateId}&${filterData}&nonce=${wcFilterAjax.nonce}`, {
                    method: "GET",
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    const productGrid = document.querySelector('.wc-filter-loop-grid');
                    if(productGrid){
                        productGrid.innerHTML = data;
                        let newUrl = window.location.pathname.replace(/\/page\/\d+\/?$/, '/');
                        history.replaceState('http://anixways.local/test/', '', `${newUrl}?${filterData}`);
                        productGrid.querySelectorAll(".wc-filter-pagination a").forEach(link => {
                            let giveURL = link.getAttribute("href");
                            link.href = giveURL.replace('/wp-admin/admin-ajax.php/', location.pathname);
                        });
                    }
                })
                .catch(error => {
                    console.error("Error applying filters:", error);
                });
            }
            console.log(filterData);
        }, 100); // Debounce for 500ms
    }

    filter.querySelectorAll("input, select, textarea").forEach(input => {
        input.addEventListener("change", applyFilters);
        input.addEventListener("input", applyFilters);
    }); 
    filter.addEventListener("submit", function(e){
        e.preventDefault();
        applyFilters(e);
    });
});