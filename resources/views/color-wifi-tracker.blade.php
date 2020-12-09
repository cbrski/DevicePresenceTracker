@switch($state)
    @case(\App\DeviceLinkStateLog::STATE_REACHABLE)
        success
    @break
    @case(\App\DeviceLinkStateLog::STATE_STALE)
    @case(\App\DeviceLinkStateLog::STATE_DELAY)
        badge wifi-stale
    @break
    @case(\App\DeviceLinkStateLog::STATE_FAILED)
    @case(\App\DeviceLinkStateLog::STATE_OFFLINE)
        danger
    @break
    @default
        secondary
@endswitch