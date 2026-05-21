import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { CheckCircle2 } from 'lucide-react';
import { useEffect, useState } from 'react';

interface TokenCreatedDialogProps {
    open: boolean;
    plainToken: string;
    message?: string | null;
    onOpenChange: (open: boolean) => void;
}

export function TokenCreatedDialog({ open, plainToken, message, onOpenChange }: TokenCreatedDialogProps) {
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        setCopied(false);
    }, [plainToken]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="border-emerald-200 sm:max-w-md">
                <DialogHeader className="space-y-3">
                    <div className="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-100">
                        <CheckCircle2 className="size-7 text-emerald-600" aria-hidden />
                    </div>
                    <DialogTitle className="text-center text-emerald-900">Token created</DialogTitle>
                    <DialogDescription className="text-center text-neutral-600">
                        {message ?? 'Copy this token now. It will not be shown again.'}
                    </DialogDescription>
                </DialogHeader>
                <code className="block rounded-lg border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-center font-mono text-lg tracking-wide text-neutral-900">
                    {plainToken}
                </code>
                <DialogFooter className="flex-col gap-2 sm:flex-col">
                    <Button
                        type="button"
                        className="w-full bg-emerald-600 text-white hover:bg-emerald-700"
                        onClick={async () => {
                            await navigator.clipboard.writeText(plainToken);
                            setCopied(true);
                        }}
                    >
                        {copied ? 'Copied to clipboard' : 'Copy to clipboard'}
                    </Button>
                    <Button type="button" variant="outline" className="w-full border-neutral-300" onClick={() => onOpenChange(false)}>
                        Done
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
