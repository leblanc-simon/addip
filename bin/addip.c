#include <string.h>
#include <unistd.h>
#include <errno.h>
#include <sys/types.h>

extern char **environ;
extern int errno;

int main (int argc, char **argv)
{
    if (argc != 3) {
        return 1;
    }

    uid_t uid, euid;

    uid = getuid();
    euid = geteuid();
    setreuid(euid, euid);

    char buf[1024];
    memset(buf, 0, sizeof(buf));

    if (readlink("/proc/self/exe", buf, sizeof(buf) - 1) == -1) {
        return 2;
    }

    strcat(buf, ".php");

    execve(buf, argv, environ);

    return errno;
}
