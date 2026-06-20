{{-- Blog fields --}}
<div class="card p-6 space-y-4">
    <h2 class="font-black text-lg">Blog Post</h2>
    <div>
        <label class="label" for="preview_text">Free Preview Text</label>
        <textarea id="preview_text" name="preview_text" rows="3" class="input"
                  placeholder="Short teaser shown to everyone before paywall...">{{ old('preview_text', $product->meta('preview_text', '')) }}</textarea>
        <div class="help">Visible to everyone. If paywalled, the rest is locked.</div>
    </div>
    <div>
        <label class="label" for="body_markdown">Full Content (Markdown)</label>
        <textarea id="body_markdown" name="body_markdown" rows="12" class="input font-mono text-sm"
                  placeholder="# Heading

Write your blog post in Markdown.

## Subheading

- List item
- Another item

**Bold**, *italic*, [link](https://...)">{{ old('body_markdown', $product->blogBody) }}</textarea>
        <div class="help">Supports Markdown: **bold**, *italic*, # heading, - list, [link](url), > quote, ```code```</div>
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_paywalled" id="is_paywalled" value="1"
               {{ old('is_paywalled', $product->isPaywalled) ? 'checked' : '' }}
               class="rounded text-brand-500 focus:ring-brand-500">
        <label for="is_paywalled" class="text-sm">Paywall full content (buyers unlock with price)</label>
    </div>
</div>