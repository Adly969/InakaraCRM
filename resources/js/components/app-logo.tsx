import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <div className="flex items-center gap-3">
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-white">
                <AppLogoIcon className="size-5 fill-current text-white" />
            </div>
            <div className="grid flex-1 text-left">
                <span className="truncate text-base leading-none font-bold text-foreground">
                    INAKARA
                </span>
                <span className="mt-0.5 truncate text-xs leading-tight text-muted-foreground">
                    Enterprise CRM
                </span>
            </div>
        </div>
    );
}
