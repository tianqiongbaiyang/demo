#命令行执行 sh reload.sh 可以执行该脚本实现swoole平滑重启
echo "loading..."
#pid=`pidof live_master`  #linux下执行方式
pid=`pgrep -f live_master` #mac下pidof没有用，改为使用pgrep -f命令或者用 ps aux |grep live_master |grep -v grep |awk '{print $2}' 命令方式，awk '{print $2}' 为打印每行的第二个字段
echo $pid
kill -USR1 $pid  # -USR1 重启 所有 Worker 进程和 TaskWorker 进程, -USR2 重启所有 Task 进程,-SIGTERM 安全终止服务器
echo "loading success"

