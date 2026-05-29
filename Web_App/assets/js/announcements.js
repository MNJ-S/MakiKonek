document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.querySelector("#announcement-search");
    const filterButtons = document.querySelectorAll("[data-category]");
    const posts = document.querySelectorAll(".announcement-post");
    let activeCategory = "all";

    const filterPosts = () => {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : "";

        posts.forEach((post) => {
            const categoryMatches = activeCategory === "all" || post.dataset.category === activeCategory;
            const textMatches = post.textContent.toLowerCase().includes(query);
            post.hidden = !(categoryMatches && textMatches);
        });
    };

    filterButtons.forEach((button) => {
        button.addEventListener("click", () => {
            filterButtons.forEach((item) => item.classList.remove("active"));
            button.classList.add("active");
            activeCategory = button.dataset.category || "all";
            filterPosts();
        });
    });

    if (searchInput) {
        searchInput.addEventListener("input", filterPosts);
    }
});
