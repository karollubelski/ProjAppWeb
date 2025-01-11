function toggleSubcategories(button) {
    const categoryItem = button.closest('.main-category');
    const subcategories = categoryItem.querySelector('.subcategories');
    const isActive = button.classList.contains('active');
    
    button.classList.toggle('active');
    
    if (subcategories) {
        subcategories.classList.toggle('active');
    }
    
    event.stopPropagation();
}

function expandSelectedCategory() {
    const urlParams = new URLSearchParams(window.location.search);
    const selectedCategory = urlParams.get('category');
    
    if (selectedCategory) {
        const selectedLink = document.querySelector(`a[href="?category=${selectedCategory}"]`);
        if (selectedLink) {
            let parent = selectedLink.closest('.subcategories');
            while (parent) {
                parent.classList.add('active');
                const button = parent.previousElementSibling.querySelector('.toggle-subcategories');
                if (button) {
                    button.classList.add('active');
                }
                parent = parent.parentElement.closest('.subcategories');
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', expandSelectedCategory);