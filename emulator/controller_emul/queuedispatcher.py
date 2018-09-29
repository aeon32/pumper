# -*- coding:utf-8 -*-
import socket

import time

import heapq
import controller_emul.enum
import controller_emul.config

#класс, хранящий задание
class Job:
    JOB_STATE = controller_emul.enum.enum( JOB_ADDED =1,        #команда добавлена
                           JOB_PROCESS=2,       #команда в работе
                           JOB_TO_DELETE =3     #команда приговорена к удалению
                        )
    def __init__ (self):
        self.nextTryStamp = 0       #время следующей попытки (время, когда должна быть вызвана функция process) 
        self.job_state = self.JOB_STATE.JOB_ADDED
    
    def getNextTryStamp(self):
        return self.nextTryStamp
    
    def getJobState(self):
        return self.job_state
    
    def process(self):
        pass
    
    def setNextTryStamp(self, nextTryStamp):
        self.nextTryStamp = nextTryStamp
        
    def setJobState(self, jobState):
        self.job_state = jobState

    

#класс, хранящий в себе очередь заданий и обрабатывающий её
class QueueDispatcher(object):
    WAIT_PROCESSING_QUEUE = 1     #время работы пустой очереди в с
    
    def __init__ (self):
        self.nextTryTime= 0
        self.job_queue = [] #очередь заданий
        self.running = True
    
    def is_time_over(self, currentTime):
        nextQueueProcessTime = self.nextTryTime if len(self.job_queue) == 0 else self.job_queue[0][0]    
        #print ("queue len %d " % (len(self.job_queue), ))

        #print ("%d %s %s %s" % (len(self.job_queue), str(self.job_queue[0][0] ), str(currentTime), str(nextQueueProcessTime)))
        #print "writeable time %s %s" % (currentTime,self.nextTryTime)
        res =  currentTime >= nextQueueProcessTime
        self.nextTryTime = currentTime + self.WAIT_PROCESSING_QUEUE
            
        return (res, nextQueueProcessTime)

    def iterate(self, currentTime):
        if len(self.job_queue) > 0:
            currentTime = time.time()
            job = heapq.heappop(self.job_queue)
            job = job[1]
            #job - экземпляр Job с наименьшем временем nextTryStamp
            if currentTime > job.getNextTryStamp():
                if (job.getJobState() == job.JOB_STATE.JOB_ADDED or job.getJobState() == job.JOB_STATE.JOB_PROCESS):
                    job.process()
                #проверяем снова состояние работы
                if (job.getJobState() == job.JOB_STATE.JOB_ADDED or job.getJobState() == job.JOB_STATE.JOB_PROCESS):
                    heapq.heappush(self.job_queue, (job.getNextTryStamp(), job))
            elif (job.getJobState() != job.JOB_STATE.JOB_TO_DELETE ):
                heapq.heappush(self.job_queue, (job.getNextTryStamp(), job))

    
    def push_job(self,job):
        heapq.heappush(self.job_queue,(job.getNextTryStamp(), job))

    def run(self, idleFunction = None):
        while self.running:
            if callable(idleFunction):
                idleFunction()
            currentTime = time.time()
            is_time_over, nextQueueProcessTime = self.is_time_over(currentTime)
            pause = nextQueueProcessTime - currentTime

            if is_time_over:
                self.iterate(currentTime)
            elif pause > 0 :
                time.sleep(pause)
                currentTime = time.time()
                self.iterate(currentTime)

    def queue_size(self):
        return len(self.job_queue)

    def terminate(self):
        self.running = False




            
            









