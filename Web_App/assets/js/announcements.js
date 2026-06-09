document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.querySelector("#announcement-search");
    const filterButtons = document.querySelectorAll(".ann-filters button[data-category]");
    const posts = document.querySelectorAll(".announcement-post");
    const paginationButtons = document.querySelectorAll(".ann-pagination button");
    const nextArrowButton = document.querySelector(".ann-pagination button[aria-label='Next page']");
    const annFeedContainer = document.querySelector(".ann-feed");

    let activeCategory = "all";
    let currentPage = 1;

    const endOfListMessage = document.createElement("div");
    endOfListMessage.className = "no-announcement-box end-of-list-box";
    endOfListMessage.innerHTML = `
        <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
        <p class="main-msg">No further announcements found</p>
        <p class="sub-msg">You've reached the end of the list. Check back later for new updates!</p>
    `;

    const noMatchMessage = document.createElement("div");
    noMatchMessage.className = "no-announcement-box no-match-box";
    noMatchMessage.innerHTML = `
        <div class="no-data-icon"><i class="fa-solid fa-magnifying-glass-blur"></i></div>
        <p class="main-msg">No matching announcements found</p>
        <p class="sub-msg">We couldn't find anything matching your search term. Try checking your spelling or selecting another category.</p>
    `;
    
    const paginationContainer = document.querySelector(".ann-pagination");
    if (annFeedContainer && paginationContainer) {
        annFeedContainer.insertBefore(endOfListMessage, paginationContainer);
        annFeedContainer.insertBefore(noMatchMessage, paginationContainer);
    }

    const filterPosts = () => {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
        let totalMatchedFilterAndSearch = 0;

        posts.forEach((post) => {
            const categoryMatches = activeCategory === "all" || post.dataset.category === activeCategory;
            
            const cardTitle = post.querySelector("h3") ? post.querySelector("h3").textContent.toLowerCase() : "";
            const cardDesc = post.querySelector("p") ? post.querySelector("p").textContent.toLowerCase() : "";
            const textMatches = cardTitle.includes(query) || cardDesc.includes(query);

            if (categoryMatches && textMatches) {
                totalMatchedFilterAndSearch++;

                if (currentPage === 1) {
                    post.style.display = "grid";
                } else {
                    post.style.display = "none";
                }
            } else {
                post.style.display = "none";
            }
        });

        if (currentPage === 2 || currentPage === 3) {
            endOfListMessage.style.display = "block";
            noMatchMessage.style.display = "none";
        } else if (totalMatchedFilterAndSearch === 0) {
            endOfListMessage.style.display = "none";
            noMatchMessage.style.display = "block";
        } else {
            endOfListMessage.style.display = "none";
            noMatchMessage.style.display = "none";
        }
    };

    filterButtons.forEach((button) => {
        button.addEventListener("click", () => {
            filterButtons.forEach((item) => item.classList.remove("active"));
            button.classList.add("active");
            
            activeCategory = button.dataset.category || "all";
            currentPage = 1;
            
            updatePaginationUI();
            filterPosts();
        });
    });

    if (searchInput) {
        searchInput.addEventListener("input", () => {
            currentPage = 1;
            updatePaginationUI();
            filterPosts();
        });
    }

    paginationButtons.forEach(button => {
        if (button !== nextArrowButton) {
            button.addEventListener("click", function() {
                currentPage = parseInt(this.textContent);
                updatePaginationUI();
                filterPosts();
            });
        }
    });

    if (nextArrowButton) {
        nextArrowButton.addEventListener("click", () => {
            if (currentPage < 3) {
                currentPage++;
                updatePaginationUI();
                filterPosts();
            }
        });
    }

    function updatePaginationUI() {
        paginationButtons.forEach(btn => {
            if (btn !== nextArrowButton) {
                if (parseInt(btn.textContent) === currentPage) {
                    btn.classList.add("active");
                } else {
                    btn.classList.remove("active");
                }
            }
        });

        if (nextArrowButton) {
            if (currentPage === 3) {
                nextArrowButton.style.opacity = "0.4";
                nextArrowButton.style.cursor = "not-allowed";
            } else {
                nextArrowButton.style.opacity = "1";
                nextArrowButton.style.cursor = "pointer";
            }
        }
    }

    filterPosts();
});
