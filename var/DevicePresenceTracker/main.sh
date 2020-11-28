#!/usr/bin/env bash

GET_NEIGHBOURS() {

	my_dir="$(dirname $0)"
	source "${my_dir}/settings.sh"

	out="{"
	out="${out}\"timestamp\":$(date +%s),"
	out="${out}\"neighbours\":["

	data=$(COMMAND_DATA)
	data_dhcp=$(COMMAND_DHCP_LEASES)
	line_count=$( echo "$data" | wc -l )
	line_current=1
	IFS='
	'
	for LINE in ${data}
	do
		IP=`echo $LINE | grep -i -E -o '^([0-9]{1,3}[\.]){3}[0-9]{1,3}'`
		DEV=`echo $LINE | grep -i -E -o '(eth|wlan)([[:graph:]]*)'`
		LLADDR=`echo $LINE | grep -i -E -o '[0-9a-f]{2}(:[0-9a-f]{2}){5}'`
		STATE=`echo $LINE | grep -E -o 'PERMANENT|NOARP|REACHABLE|STALE|NONE|INCOMPLETE|DELAY|PROBE|FAILED'`


		out="${out}{"
		out="${out}\"ip\":"
		if [ -z $IP ]
		then
			out="${out}null"
		else
			out="${out}\"${IP}\""
		fi
		out="${out},"


		out="${out}\"dev\":"
		if [ -z $DEV ]
		then
			out="${out}null"
		else
			out="${out}\"${DEV}\""
		fi
		out="${out},"


		out="${out}\"lladdr\":"
		if [ -z $LLADDR ]
		then
			out="${out}null"
		else
			out="${out}\"${LLADDR}\""
		fi
		out="${out},"


		out="${out}\"state\":"
		if [ -z $STATE ]
		then
			out="${out}null"
		else
			out="${out}\"${STATE}\""
		fi
		out="${out},"


		out="${out}\"hostname\":"
		HOSTNAME=$( echo "$data_dhcp" | grep -i -E ${LLADDR} | awk '{print $4}' )
		if [ -z $HOSTNAME ]
		then
			HOSTNAME=$( echo "$data_dhcp" | grep -i -E ${IP} | awk '{print $4}' )
		fi
		if [ -z $HOSTNAME ]
		then
			out="${out}null"
		else
			out="${out}\"${HOSTNAME}\""
		fi
		out="${out}}"


		if [ ! $line_current -eq $line_count ]
		then
			out="${out},"
		fi


		line_current=$(( line_current+1 ))


		# if it is likely that the wire connected device is no longer using network,
		# we will speed up the "state=FAILED" result which will be visible next time

		if [ "$DEV" != "eth0" ] && [ "$DEV" != "wlan0" ] && [ "$STATE" == "STALE" ]
		then
			ping -c 1 ${IP} >/dev/null &
		fi
	done
	IFS=' '

	out="${out}]"

	out="${out}}"
	echo $out
}

