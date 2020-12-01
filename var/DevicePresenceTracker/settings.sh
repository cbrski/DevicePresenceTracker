#!/usr/bin/env bash

COMMAND_IP_NEIGH() {
	ip neigh | sort
}

COMMAND_DHCP_LEASES() {
	cat /tmp/dhcp.leases*
}

COMMAND_IWINFO_ASSOCLIST() {
	iwinfo wlan0 assoclist
}

