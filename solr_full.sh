#!/bin/bash
#{  win下先关闭
    echo full begin `date` ============================================================
    starttime=`date  +'%Y-%m-%d_%H:%M:%S'`
    #锁 win下先关闭
#    flock -n 30

    [ $? -eq 1 ] && { echo fail; exit; }
    echo $$

    #环境变量
#    env_base_dir="/usr/local/sbin/"
#    env=$env_base_dir"cron/"$domain
#    source "$env"

    log_file=${ETL_LOG_DIR}"/solr-full.log"

    xml_dir=${SOLR_XML_DIR}
    xml_file_pre=${xml_dir}"full_import_"
    
    #生成xml 
    echo `date` : begin etl php  |tee -a $log_file
    php ${WEB_PHP_DIR}"/index.php" CronEtl full_import
    echo `date` : end etl php |tee -a $log_file

    l_m=`cat ${xml_file_pre}*.xml | wc -l` #xml 行数统计
    echo `date` : file $xml_file count: $l_m |tee -a $log_file
    if [ $l_m -le 2 ] 
        then 
            endtime=`date  +'%Y-%m-%d %H:%M:%S'`
            echo `date` : "starttime:$starttime   endtime:$endtime"|tee -a $log_file
            echo `date` : delta end `date` ============================================================no update|tee -a $log_file
        exit 1
    fi

    #备份索引文件
    curl http://${SOLR_MASTER_HOST}:${SOLR_MASTER_PORT}/solr/${SOLR_CORE}/replication?command=backup
    #生成索引
    #多线程提交
    commit_files=`ls  -l ${xml_file_pre}*.xml|awk '{print $NF}'`
    for commit_file in $commit_files
    do
    {
        echo "commit_file:$commit_file"
        {
            #curl http://${MSEARCH_SOLR_MASTER_HOST}:${MSEARCH_SOLR_MASTER_PORT}/solr/core_msearch/update?commit=false -H"Content-Type: text/xml" --data-binary @${commit_file}
            curl http://${SOLR_MASTER_HOST}:${SOLR_MASTER_PORT}/solr/${SOLR_CORE}/update?commit=false -F stream.file=${commit_file}
        }&
    }
    done
    #等待子进程结束
    wait
    curl http://${SOLR_MASTER_HOST}:${SOLR_MASTER_PORT}/solr/${SOLR_CORE}/update?commit=true
    
    #删除xml文件
    find $xml_dir  -type f -name "${xml_file_pre}*" -ctime +2 -exec rm '{}' \;
    #清除索引备份文件 本地win 请求远程solr，win下先关闭本功能
    #rm -rf `find "${SOLR_DATA_DIR}/data"  -type d -name "snapshot.*" | sort | head -n -4`

    #结束
    endtime=`date  +'%Y-%m-%d %H:%M:%S'`
    echo `date` : "starttime:$starttime   endtime:$endtime"|tee -a $log_file
    echo `date` : delta end `date` ============================================================|tee -a $log_file

#} 30<>/tmp/update_lock  win下先关闭


