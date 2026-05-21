import { useLayoutEffect } from 'react';

/**
 * ISI PLAZA panel always uses a light UI. If the user enabled dark mode elsewhere
 * in the app, remove it while this page is mounted so inputs and carets stay visible.
 */
export function useIsiPlazaLightShell(): void {
    useLayoutEffect(() => {
        const root = document.documentElement;
        const hadDark = root.classList.contains('dark');

        root.classList.remove('dark');

        return () => {
            if (hadDark) {
                root.classList.add('dark');
            }
        };
    }, []);
}
