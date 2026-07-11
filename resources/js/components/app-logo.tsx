import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <div className="flex items-center gap-3">
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-white">
                <AppLogoIcon className="size-5 fill-current text-white" />
            </div>
            <div className="grid flex-1 text-left">
                <span className="truncate leading-none font-bold text-base text-foreground">
                    INAKARA
                </span>
                <span className="truncate text-xs text-muted-foreground leading-tight mt-0.5">
                    Enterprise CRM
                </span>
            </div>
        </div>
    );
}
