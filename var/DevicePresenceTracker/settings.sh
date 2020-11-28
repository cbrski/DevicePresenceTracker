#!/usr/bin/env bash

COMMAND_DATA() {
	ip neigh | sort
}

COMMAND_DHCP_LEASES() {
	cat /tmp/dhcp.leases*
}

