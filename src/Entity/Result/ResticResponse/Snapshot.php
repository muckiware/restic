<?php

namespace MuckiRestic\Entity\Result\ResticResponse;

class Snapshot
{
    protected string $id;
    protected string $short_id;
    protected string $time;
    protected string $tree;
    protected string $parent;
    /**
     * @var array <string>
     */
    protected array $paths;
    protected string $hostname;
    protected string $username;
    protected string $uid;
    protected string $gid;
    protected string $program_version;
    protected Summary $summary;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getShortId(): string
    {
        return $this->short_id;
    }

    public function setShortId(string $short_id): void
    {
        $this->short_id = $short_id;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    public function getTree(): string
    {
        return $this->tree;
    }

    public function setTree(string $tree): void
    {
        $this->tree = $tree;
    }

    public function getParent(): string
    {
        return $this->parent;
    }

    public function setParent(string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return array <string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param array <string> $paths
     */
    public function setPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): void
    {
        $this->hostname = $hostname;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    public function getGid(): string
    {
        return $this->gid;
    }

    public function setGid(string $gid): void
    {
        $this->gid = $gid;
    }

    public function getProgramVersion(): string
    {
        return $this->program_version;
    }

    public function setProgramVersion(string $program_version): void
    {
        $this->program_version = $program_version;
    }

    public function getSummary(): Summary
    {
        return $this->summary;
    }

    public function setSummary(Summary $summary): void
    {
        $this->summary = $summary;
    }
}
