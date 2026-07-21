{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($routes as $routeName)
  <url>
    <loc>{{ route($routeName) }}</loc>
    <changefreq>{{ $routeName === 'home' ? 'weekly' : 'monthly' }}</changefreq>
    <priority>{{ $routeName === 'home' ? '1.0' : '0.7' }}</priority>
  </url>
@endforeach
</urlset>
