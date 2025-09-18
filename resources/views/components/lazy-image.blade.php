@props([
    'src' => '',
    'alt' => '',
    'class' => '',
    'placeholder' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y5ZmFmYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0iY2VudHJhbCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzlDQTNBRiI+TG9hZGluZy4uLjwvdGV4dD48L3N2Zz4='
])

<img
    {{ $attributes->merge(['class' => $class]) }}
    src="{{ $placeholder }}"
    data-src="{{ $src }}"
    alt="{{ $alt }}"
    loading="lazy"
    x-data="{ loaded: false }"
    x-init="
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.onload = () => loaded = true;
                    observer.unobserve(img);
                }
            });
        }, { threshold: 0.1 });
        observer.observe($el);
    "
    :class="{ 'opacity-100': loaded, 'opacity-70': !loaded }"
    style="transition: opacity 0.3s ease-in-out;"
>