<!-- Footer -->
<footer class="kt-footer">
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="flex flex-col items-center justify-center gap-3 py-5 md:flex-row md:justify-between">
            <div class="order-2 flex gap-2 text-sm font-normal md:order-1">
                <span class="text-secondary-foreground">
                    {{ date('Y') }}©
                </span>
                <a class="hover:text-primary text-secondary-foreground" href="#">
                    Soft-Optimum-Services
                </a>
            </div>
            <nav class="order-1 flex gap-4 text-sm font-normal text-secondary-foreground md:order-2">
                <a class="hover:text-primary" href="{{ route('public.documentation') }}" data-ajax="false">
                    Documentation
                </a>
                <a class="hover:text-primary" href="{{ route('public.faq') }}" data-ajax="false">
                    FAQ
                </a>
                <a class="hover:text-primary" href="{{ route('public.support') }}" data-ajax="false">
                    Support
                </a>
                <a class="hover:text-primary" href="{{ route('public.license') }}" data-ajax="false">
                    License
                </a>
            </nav>
        </div>
    </div>
    <!-- End of Container -->
</footer>
<!-- End of Footer -->
